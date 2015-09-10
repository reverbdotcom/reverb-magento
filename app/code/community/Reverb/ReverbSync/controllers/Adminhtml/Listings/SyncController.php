<?php

require_once('Reverb/ReverbSync/controllers/Adminhtml/BaseController.php');
class Reverb_ReverbSync_Adminhtml_Listings_SyncController extends Reverb_ReverbSync_Adminhtml_BaseController
{
    const BULK_SYNC_EXCEPTION = 'An uncaught exception occurred while executing the Reverb Bulk Product Sync via the admin panel: %s';
    const SUCCESS_BULK_SYNC_COMPLETED = 'Reverb Bulk product sync process completed.';
    const SUCCESS_BULK_SYNC_QUEUED_UP = '%s products have been queued to be synced with Reverb';
    const EXCEPTION_STOP_BULK_SYNC = 'An exception occurred while attempting to stop all reverb listing sync tasks: %s';
    const SUCCESS_STOPPED_LISTING_SYNCS = 'Stopped all pending Reverb Listing Sync tasks';

    protected $_adminHelper = null;

    public function bulkSyncAction()
    {
        try
        {
            Mage::helper('ReverbSync/sync_product')->deleteAllListingSyncTasks();
            $number_of_syncs_queued_up = Mage::helper('ReverbSync/sync_product')->queueUpBulkProductDataSync();
        }
        catch(Reverb_ReverbSync_Model_Exception_Redirect $redirectException)
        {
            // Error message should already have been logged and redirect should already have been set in
            // Reverb_ReverbSync_Helper_Admin::throwRedirectException(). Throw the exception again
            // so that the Magento Front controller dispatch() method can handle redirect
            throw $redirectException;
        }
        catch(Exception $e)
        {
            // We don't know what caused this exception. Log it and throw redirect exception
            $error_message = $this->__(self::BULK_SYNC_EXCEPTION, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message, 'reverbSync/adminhtml_sync/index');
        }

        $success_message = $this->__(self::SUCCESS_BULK_SYNC_QUEUED_UP, $number_of_syncs_queued_up);
        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect('adminhtml/reports_reverbreport/index');
    }

    public function stopBulkSyncAction()
    {
        try
        {
            $rows_deleted = Mage::helper('ReverbSync/sync_product')->deleteAllListingSyncTasks();
        }
        catch(Reverb_ReverbSync_Model_Exception_Redirect $redirectException)
        {
            // Error message should already have been logged and redirect should already have been set in
            // Reverb_ReverbSync_Helper_Admin::throwRedirectException(). Throw the exception again
            // so that the Magento Front controller dispatch() method can handle redirect
            throw $redirectException;
        }
        catch(Exception $e)
        {
            // We don't know what caused this exception. Log it and throw redirect exception
            $error_message = $this->__(self::EXCEPTION_STOP_BULK_SYNC, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message, 'reverbSync/adminhtml_sync/index');
        }

        $success_message = $this->__(self::SUCCESS_STOPPED_LISTING_SYNCS);
        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect('adminhtml/reports_reverbreport/index');
    }

    public function getBlockToShow()
    {
        $are_product_syncs_pending = $this->areProductSyncsPending();
        $index_block = $are_product_syncs_pending ? '/adminhtml_listings_index_syncing' : '/adminhtml_listings_index';
        return $this->getModuleBlockGroupname() . $index_block;
    }

    public function areProductSyncsPending()
    {
        $outstandingListingSyncTasksCollection = Mage::helper('reverb_process_queue/task_processor')
                                                    ->getQueueTasksForProgressScreen('listing_sync');
        $outstanding_tasks_array = $outstandingListingSyncTasksCollection->getItems();

        return (!empty($outstanding_tasks_array));
    }

    public function getControllerDescription()
    {
        return "Reverb Product Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'catalog/reverb_sync';
    }
}
