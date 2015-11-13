<?php

class Reverb_ReverbSync_Block_Adminhtml_Orders_Unique_Index extends Reverb_ReverbSync_Block_Adminhtml_Orders_Index
{
    public function __construct()
    {
        parent::__construct();

        $sync_shipment_tracking_action_url = Mage::getModel('adminhtml/url')
                                                ->getUrl('adminhtml/ReverbSync_orders_sync_unique/syncShipmentTracking');

        $this->_addButton('sync_shipment_tracking', array(
                'label' => Mage::helper('ReverbSync')->__('Sync Shipment Tracking Data With Reverb'),
                'onclick' => "document.location='" .$sync_shipment_tracking_action_url . "'",
                'level' => -1
            )
        );
    }

    protected function _retrieveAndProcessTasksButtonLabel()
    {
        return 'Download and Create New Orders';
    }

    protected function _processDownloadedTasksButtonLabel()
    {
        return 'Create Downloaded Orders';
    }

    protected function _getBulkSyncUrlParams()
    {
        return array('redirect_controller' => 'ReverbSync_orders_sync_unique');
    }

    protected function _getHeaderTextTemplate()
    {
        return '%s of %s Reverb Order Creation and Shipment Tracking Tasks have completed syncing with Magento';
    }

    protected function _getTaskCode()
    {
        return array('order_creation', Reverb_ReverbSync_Model_Sync_Shipment_Tracking::JOB_CODE);
    }

    protected function _getTaskProcessorHelper()
    {
        return Mage::helper('reverb_process_queue/task_processor_unique');
    }
}
