<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

class Reverb_ReverbSync_Model_Mysql4_Task_Listing extends Mage_Core_Model_Mysql4_Abstract
{
    const SERIALIZED_ARGUMENTS_OBJECT = 'O:13:"Varien_Object":7:{s:8:"*_data";a:1:{s:10:"product_id";s:14:"##PRODUCT_ID##";}s:18:"*_hasDataChanges";b:1;s:12:"*_origData";N;s:15:"*_idFieldName";N;s:13:"*_isDeleted";b:0;s:16:"*_oldFieldsMap";a:0:{}s:17:"*_syncFieldsMap";a:0:{}}';

    public function _construct()
    {
        $this->_init('reverb_process_queue/task','task_id');
    }

    public function queueListingSyncsByProductIds(array $product_ids_in_system)
    {
        $insert_data_array_template = $this->_getInsertDataArrayTemplate();
        $data_array_to_insert = array();
        foreach ($product_ids_in_system as $product_id)
        {
            $product_data_array = $insert_data_array_template;
            $serialized_arguments = str_replace('##PRODUCT_ID##', $product_id, self::SERIALIZED_ARGUMENTS_OBJECT);
            $product_data_array['serialized_arguments_object'] = $serialized_arguments;

            $data_array_to_insert[] = $product_data_array;
        }

        $columns_array = $this->_getInsertColumnsArray();

        $number_of_created_rows = $this->_getWriteAdapter()->insertArray(
            $this->getMainTable(), $columns_array, $data_array_to_insert
        );

        return $number_of_created_rows;
    }

    protected function _getInsertColumnsArray()
    {
        return array('code', 'status', 'object', 'method', 'serialized_arguments_object');
    }

    protected function _getInsertDataArrayTemplate()
    {
        return array(
            'code' => 'listing_sync',
            'status' => Reverb_ProcessQueue_Model_Task::STATUS_PENDING,
            'object' => 'ReverbSync/sync_product',
            'method' => 'executeQueuedIndividualProductDataSync'
        );
    }
}
