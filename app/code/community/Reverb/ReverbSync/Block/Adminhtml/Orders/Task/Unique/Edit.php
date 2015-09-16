<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Unique_Edit
    extends Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Edit
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_orders_task_unique';
    }
}
