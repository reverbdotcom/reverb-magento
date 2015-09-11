<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Model_Mysql4_Task_Order_Update extends Reverb_ReverbSync_Model_Mysql4_Task
{
    const ORDER_UPDATE_OBJECT = 'reverbSync/sync_order_update';
    const ORDER_UPDATE_METHOD = 'updateReverbOrderInMagento';

    protected $_task_code = 'order_update';

    public function queueOrderUpdateByReverbOrderDataObject(stdClass $orderDataObject)
    {
        $order_number = $orderDataObject->order_number;

        $insert_data_array = $this->_getInsertDataArrayTemplate(self::ORDER_UPDATE_OBJECT,
                                                                self::ORDER_UPDATE_METHOD, $order_number);

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
