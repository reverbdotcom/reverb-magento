<?php

class Reverb_ReverbSync_Block_Adminhtml_Orders_Unique_Index extends Reverb_ReverbSync_Block_Adminhtml_Orders_Index
{
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('bulk_orders_sync', 'sync_downloaded_tasks');

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
        return '';
    }

    protected function _processDownloadedTasksButtonLabel()
    {
        return '';
    }

    protected function _getHeaderTextTemplate()
    {
        return '%s of %s Reverb Shipment Tracking Tasks have completed syncing with Magento';
    }

    protected function _getTaskCode()
    {
        return array(Reverb_ReverbSync_Model_Sync_Shipment_Tracking::JOB_CODE);
    }

    protected function _getTaskProcessorHelper()
    {
        return Mage::helper('reverb_process_queue/task_processor_unique');
    }
}
