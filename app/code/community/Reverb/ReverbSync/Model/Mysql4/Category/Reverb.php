<?php
/**
 * Author: Sean Dunagan
 * Created: 10/26/15
 */

class Reverb_ReverbSync_Model_Mysql4_Category_Reverb extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_database_insert_columns_array
                = array('name', 'description', 'reverb_product_type_slug', 'reverb_category_slug');

    public function _construct()
    {
        $this->_init('reverbSync/reverb_category','reverb_category_id');
    }

    public function initializeReverbCategoriesTable()
    {
        $this->truncateReverbCategoriesTable();
        $array_of_reverb_categories = $this->getReverbCategoryORMArrays();
        $this->loadReverbCategoriesArrayIntoDatabase($array_of_reverb_categories);
    }

    public function loadReverbCategoriesArrayIntoDatabase(array $array_of_reverb_categories)
    {
        return $this->_getWriteAdapter()->insertArray($this->getMainTable(), $this->_database_insert_columns_array,
                                                            $array_of_reverb_categories);
    }

    public function getReverbCategoryORMArrays()
    {
        return Mage::getSingleton('reverbSync/source_reverb_categories')
                                        ->getArrayOfReverbCategoriesForDatabaseLoad();
    }

    public function truncateReverbCategoriesTable()
    {
        return Mage::getResourceSingleton('reverbSync/category_reverb')->deleteAllCategories();
    }

    public function deleteAllCategories()
    {
        $rows_deleted = $this->_getWriteAdapter()->delete($this->getMainTable());
        return $rows_deleted;
    }
}
