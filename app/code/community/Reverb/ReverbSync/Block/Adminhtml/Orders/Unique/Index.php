<?php

class Reverb_ReverbSync_Block_Adminhtml_Orders_Unique_Index extends Reverb_ReverbSync_Block_Adminhtml_Orders_Index
{
    protected function _getBulkSyncUrlParams()
    {
        return array('redirect_controller' => 'adminhtml_orders_sync_unique');
    }

    protected function _getHeaderTextTemplate()
    {
        return '%s of %s Reverb Order Creation Tasks have completed syncing with Magento';
    }

    protected function _getTaskCode()
    {
        return 'order_creation';
    }

    protected function _getTaskProcessorHelper()
    {
        return Mage::helper('reverb_process_queue/task_processor_unique');
    }
}
