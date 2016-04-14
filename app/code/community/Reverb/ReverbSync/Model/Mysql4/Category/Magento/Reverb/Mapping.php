<?php
/**
 * Author: Sean Dunagan
 * Created: 10/27/15
 */

/**
 * This class has been deprecated and is no longer in use
 */
class Reverb_ReverbSync_Model_Mysql4_Category_Magento_Reverb_Mapping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('reverbSync/magento_reverb_category_mapping', 'xref_id');
    }

    /**
     * @return array
     */
    public function getAllCategoryMappings()
    {
        $table_name = $this->getMainTable();
        $readConnection = $this->getReadConnection();

        $fields_to_select = array(Reverb_ReverbSync_Model_Category_Magento_Reverb_Mapping::MAGENTO_CATEGORY_ID_FIELD,
                                    Reverb_ReverbSync_Model_Category_Magento_Reverb_Mapping::REVERB_CATEGORY_ID_FIELD);

        $select = $readConnection->select()->from($table_name, $fields_to_select);

        $reverb_category_ids_by_magento_category_ids = $readConnection->fetchPairs($select);
        return $reverb_category_ids_by_magento_category_ids;
    }
/*
    const MAGENTO_CATEGORY_ID_FIELD = 'magento_category_id';
    const REVERB_CATEGORY_ID_FIELD = 'reverb_category_id';

    protected $_database_insert_columns_array = array(self::MAGENTO_CATEGORY_ID_FIELD, self::REVERB_CATEGORY_ID_FIELD);


    public function getReverbCategoryIdsByMagentoCategoryIds(array $magento_category_ids)
    {
        $readConnection = $this->getReadConnection();

        $where_clause = self::MAGENTO_CATEGORY_ID_FIELD . ' in (?)';

        $select = $readConnection
                    ->select()
                    ->from($this->getMainTable(), self::REVERB_CATEGORY_ID_FIELD)
                    ->where($where_clause, $magento_category_ids);

        $reverb_category_ids = $readConnection->fetchCol($select);
        return $reverb_category_ids;
    }

    public function redefineCategoryMapping(array $magento_reverb_category_mapping)
    {
        $this->_truncateTable();
        array_walk($magento_reverb_category_mapping, 'Reverb_ReverbSync_Model_Mysql4_Category_Magento_Reverb_Mapping::convertToArray');
        return $this->loadMagentoReverbCategoryMappingArrayIntoDatabase($magento_reverb_category_mapping);
    }

    public static function convertToArray(&$array_item, $key)
    {
        $value = intval($array_item);
        $array_item = array(self::MAGENTO_CATEGORY_ID_FIELD => $key, self::REVERB_CATEGORY_ID_FIELD => $value);
    }

    public function loadMagentoReverbCategoryMappingArrayIntoDatabase(array $magento_reverb_category_mapping)
    {
        return $this->_getWriteAdapter()->insertArray($this->getMainTable(), $this->_database_insert_columns_array,
                                                      $magento_reverb_category_mapping);
    }

    public function getArrayMappingMagentoCategoryIdToReverbCategoryId()
    {
        $readConnection = $this->getReadConnection();

        $select = $readConnection
                    ->select()
                    ->from($this->getMainTable(),
                            array(self::MAGENTO_CATEGORY_ID_FIELD, self::REVERB_CATEGORY_ID_FIELD));


        $magento_category_id_to_reverb_category_id_mapping_array = $readConnection->fetchPairs($select);
        return $magento_category_id_to_reverb_category_id_mapping_array;
    }

    protected function _truncateTable()
    {
        $rows_deleted = $this->_getWriteAdapter()->delete($this->getMainTable());
        return $rows_deleted;
    }
*/
}
