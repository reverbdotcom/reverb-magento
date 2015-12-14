<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

class Reverb_ReverbSync_Model_Mysql4_Task_Listing extends Mage_Core_Model_Mysql4_Abstract
{
    const LISTING_TASK_CODE = 'listing_sync';

    const SERIALIZED_ARGUMENTS_OBJECT = 'O:8:"stdClass":1:{s:10:"product_id";i:##PRODUCT_ID##;}';

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

    public function deleteAllListingSyncTasks()
    {
        $where_condition_array = array('code=?' => self::LISTING_TASK_CODE);
        $rows_deleted = $this->_getWriteAdapter()->delete($this->getMainTable(), $where_condition_array);
        return $rows_deleted;
    }

    public function deleteSuccessfulTasks()
    {
        return Mage::getResourceSingleton('reverb_process_queue/task')->deleteSuccessfulTasks(self::LISTING_TASK_CODE);
    }

    protected function _getInsertColumnsArray()
    {
        return array('code', 'status', 'object', 'method', 'serialized_arguments_object');
    }

    protected function _getInsertDataArrayTemplate()
    {
        return array(
            'code' => self::LISTING_TASK_CODE,
            'status' => Reverb_ProcessQueue_Model_Task::STATUS_PENDING,
            'object' => 'reverbSync/sync_product',
            'method' => 'executeQueuedIndividualProductDataSync'
        );
    }
}
