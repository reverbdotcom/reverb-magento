<?php
/**
 * Author: Sean Dunagan
 * Created: 10/26/15
 */

class Reverb_ReverbSync_Model_Source_Reverb_Categories
{
    const REVERB_CATEGORY_FILE = 'reverb_categories.json';

    protected $_reverbCategorySingleton = null;

    public function getReverbCategoriesJson()
    {
        $json_file_path = Mage::getModuleDir('data', 'Reverb_ReverbSync') . DS . self::REVERB_CATEGORY_FILE;
        $json_string = file_get_contents($json_file_path);
        $jsonObject = json_decode($json_string);
        return $jsonObject;
    }

    public function getArrayOfReverbCategoriesForDatabaseLoad()
    {
        $array_of_orm_data_arrays = array();
        $reverbCategoriesJsonObject = Mage::getModel('reverbSync/source_reverb_categories')->getReverbCategoriesJson();
        $this->_addAllSubcategories($array_of_orm_data_arrays, $reverbCategoriesJsonObject);
        return $array_of_orm_data_arrays;
    }

    protected function _addAllSubcategories(&$array_of_orm_data_arrays, $jsonObject, $name_prefix_array = array(), $level = 1)
    {
        if ($level != 1)
        {
            $subcategories_array = isset($jsonObject->subcategories) ? $jsonObject->subcategories : null;
        }
        else
        {
            $subcategories_array = isset($jsonObject->categories) ? $jsonObject->categories : null;
        }

        if (!is_array($subcategories_array) || empty($subcategories_array))
        {
            return $array_of_orm_data_arrays;
        }

        $next_level = $level + 1;
        foreach ($subcategories_array as $subcategoryJsonObject)
        {
            $subcategory_orm_data_array = $this->_getReverbCategorySingleton()
                                            ->convertJsonObjectArrayToORMDataArray($subcategoryJsonObject);
            $subcategory_name_array = $name_prefix_array;

            $subcategory_name = $subcategory_orm_data_array['name'];
            if ($level > 1)
            {
                $subcategory_name_array[] = $subcategory_name;
                $subcategory_name = implode(' > ', $subcategory_name_array);
                $subcategory_orm_data_array['name'] = $subcategory_name;
            }
            else
            {
                $subcategory_name_array[] = $subcategory_name;
            }

            $array_of_orm_data_arrays[] = $subcategory_orm_data_array;

            $this->_addAllSubcategories($array_of_orm_data_arrays, $subcategoryJsonObject, $subcategory_name_array, $next_level);
        }

        return $array_of_orm_data_arrays;
    }

    protected function _getReverbCategorySingleton()
    {
        if (is_null($this->_reverbCategorySingleton))
        {
            $this->_reverbCategorySingleton = Mage::getSingleton('reverbSync/category_reverb');
        }

        return $this->_reverbCategorySingleton;
    }
}
