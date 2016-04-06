<?php
/**
 * Author: Sean Dunagan
 * Created: 10/26/15
 */

class Reverb_ReverbSync_Model_Mysql4_Category_Reverb extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_database_insert_columns_array
                = array('name', 'description', 'reverb_category_slug', 'reverb_product_type_slug');

    public function _construct()
    {
        $this->_init('reverbSync/reverb_category','reverb_category_id');
    }

    /**
     * Returns an array which has the id column as keys and the uuid column as values
     */
    public function getIdToUuidMapping()
    {
        $table_name = $this->getMainTable();
        $readConnection = $this->getReadConnection();

        $select_fields = array(Reverb_ReverbSync_Model_Category_Reverb::PRIMARY_KEY_FIELD,
                                Reverb_ReverbSync_Model_Category_Reverb::UUID_FIELD);

        $select = $readConnection
                    ->select()
                    ->from($table_name, $select_fields);

        $category_id_to_uuid_mapping = $readConnection->fetchPairs($select);
        return $category_id_to_uuid_mapping;
    }

    public function initializeReverbCategoriesTable()
    {
        $this->_truncateReverbCategoriesTable();
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

    protected function _truncateReverbCategoriesTable()
    {
        $rows_deleted = $this->_getWriteAdapter()->delete($this->getMainTable());
        return $rows_deleted;
    }
}
