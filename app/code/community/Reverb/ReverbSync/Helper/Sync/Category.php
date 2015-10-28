<?php
/**
 * Author: Sean Dunagan
 * Created: 10/26/15
 */

class Reverb_ReverbSync_Helper_Sync_Category
{
    const FORM_ELEMENT_FIELD_NAME_TEMPLATE = '%s[%s]';
    const REVERB_CATEGORY_MAP_ELEMENT_NAME = 'reverb_category_map';
    const NO_CATEGORY_CHOSEN_OPTION = 'none';

    protected $_reverb_category_options_array = null;

    public function getMagentoReverbCategoryMapElementArrayName()
    {
        return self::REVERB_CATEGORY_MAP_ELEMENT_NAME;
    }

    public function getReverbCategoryMapFormElementName($field)
    {
        return sprintf(self::FORM_ELEMENT_FIELD_NAME_TEMPLATE, self::REVERB_CATEGORY_MAP_ELEMENT_NAME, $field);
    }

    public function getNoCategoryChosenOption()
    {
        return self::NO_CATEGORY_CHOSEN_OPTION;
    }

    public function processMagentoReverbCategoryMapping(array $reverb_magento_category_mapping)
    {
        // Filter out all values in the array which are set to the NO_CATEGORY_CHOSEN_OPTION value
        $defined_category_mapping
            = array_filter($reverb_magento_category_mapping,
                             'Reverb_ReverbSync_Helper_Sync_Category::filter_out_no_category_chosen_option');

        if (!empty($defined_category_mapping))
        {
            Mage::getResourceSingleton('reverbSync/category_magento_reverb_mapping')
                ->redefineCategoryMapping($defined_category_mapping);
        }
    }

    static public function filter_out_no_category_chosen_option($mapped_value)
    {
        return strcmp($mapped_value, self::NO_CATEGORY_CHOSEN_OPTION);
    }

    public function getReverbCategorySelectOptionsArray()
    {
        $reverb_category_select_options_array = array();
        $reverb_category_select_options_array[self::NO_CATEGORY_CHOSEN_OPTION] = '';
        $reverb_categories = $this->getReverbCategoriesArray();

        foreach($reverb_categories as $reverbCategory)
        {
            $reverb_category_select_options_array[$reverbCategory->getId()] = $reverbCategory->getName();
        }

        return $reverb_category_select_options_array;
    }

    public function getReverbCategoriesArray()
    {
        if (is_null($this->_reverb_category_options_array))
        {
            $reverbCategoryCollection = Mage::getModel('reverbSync/category_reverb')
                                            ->getCollection();

            $this->_reverb_category_options_array = $reverbCategoryCollection->getItems();
        }

        return $this->_reverb_category_options_array;
    }
}
