<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Mysql4_Task_Order extends Reverb_ReverbSync_Model_Mysql4_Task_Unique
{
    const ORDER_CREATION_OBJECT = 'reverbSync/sync_order';
    const ORDER_CREATION_METHOD = 'createReverbOrderInMagento';

    protected $_task_code = 'order_creation';

    public function _construct()
    {
        $this->_init('reverb_process_queue/task_unique','task_id');
    }

    public function queueOrderCreationByReverbOrderDataObject(stdClass $orderDataObject)
    {
        $order_number = $orderDataObject->order_number;

        $insert_data_array = $this->_getUniqueInsertDataArrayTemplate(self::ORDER_CREATION_OBJECT,
                                                                        self::ORDER_CREATION_METHOD, $order_number);

        $serialized_arguments_object = serialize($orderDataObject);
        $insert_data_array['serialized_arguments_object'] = $serialized_arguments_object;

        $number_of_inserted_rows = $this->_getWriteAdapter()->insert($this->getMainTable(), $insert_data_array);

        return $number_of_inserted_rows;
    }

    public function getTaskCode()
    {
        return $this->_task_code;
    }
}
