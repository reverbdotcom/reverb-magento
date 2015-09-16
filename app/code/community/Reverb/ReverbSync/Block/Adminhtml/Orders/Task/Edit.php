<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Edit
    extends Reverb_ProcessQueue_Block_Adminhtml_Task_Edit
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_orders_task';
        $this->_blockGroup = 'ReverbSync';

        $this->_removeButton('delete');

        if (!$this->getAction()->canAdminUpdateStatus())
        {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        }
    }
}
