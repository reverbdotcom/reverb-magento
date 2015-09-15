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

        if (!Mage::helper('ReverbSync/orders_sync')->canAdminUpdateOrderCreationSyncStatus())
        {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        }
    }
}
