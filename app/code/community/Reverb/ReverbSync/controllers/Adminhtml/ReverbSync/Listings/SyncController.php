<?php

require_once('Reverb/ReverbSync/controllers/Adminhtml/BaseController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Listings_SyncController extends Reverb_ReverbSync_Adminhtml_BaseController
{
    const BULK_SYNC_EXCEPTION = 'Error executing the Reverb Bulk Product Sync via the admin panel: %s';
    const EXCEPTION_CLEARING_ALL_LISTING_TASKS = 'An error occurred while clearing all listing tasks from the system: %s';
    const ERROR_CLEARING_SUCCESSFUL_SYNC = 'An error occurred while clearing successful listing syncs: %s';
    const SUCCESS_BULK_SYNC_QUEUED_UP = '%s products have been queued for sync. Please wait a few minutes and refresh this page...';
    const EXCEPTION_STOP_BULK_SYNC = 'Error attempting to stop all reverb listing sync tasks: %s';
    const SUCCESS_STOPPED_LISTING_SYNCS = 'Stopped all pending Reverb Listing Sync tasks';
    const SUCCESS_CLEAR_LISTING_SYNCS = 'All listing sync tasks have been deleted';
    const SUCCESS_CLEAR_SUCCESSFUL_LISTING_SYNCS = 'All successful listing sync tasks have been deleted';

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
            $this->_getAdminHelper()->throwRedirectException($error_message, $this->_getRedirectPath());
        }

        $success_message = $this->__(self::SUCCESS_BULK_SYNC_QUEUED_UP, $number_of_syncs_queued_up);
        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect($this->_getRedirectPath());
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
            $this->_getAdminHelper()->throwRedirectException($error_message, $this->_getRedirectPath());
        }

        $success_message = $this->__(self::SUCCESS_STOPPED_LISTING_SYNCS);
        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect($this->_getRedirectPath());
    }

    public function clearAllTasksAction()
    {
        try
        {
            $listing_sync_rows_deleted = Mage::helper('ReverbSync/sync_product')->deleteAllListingSyncTasks();
            $reverb_report_rows_deleted = Mage::helper('ReverbSync/sync_product')->deleteAllReverbReportRows();
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_CLEARING_ALL_LISTING_TASKS, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message, $this->_getRedirectPath());
        }

        $success_message = $this->__(self::SUCCESS_CLEAR_LISTING_SYNCS);
        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect($this->_getRedirectPath());
    }

    public function clearSuccessfulTasksAction()
    {
        try
        {
            $listing_tasks_deleted = Mage::getResourceSingleton('reverbSync/task_listing')->deleteSuccessfulTasks();
            $reverb_report_rows_deleted = Mage::getResourceSingleton('reverb_reports/reverbreport')
                                            ->deleteSuccessfulSyncs();
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::ERROR_CLEARING_SUCCESSFUL_SYNC, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message, $this->_getRedirectPath());
        }

        $success_message = $this->__(self::SUCCESS_CLEAR_SUCCESSFUL_LISTING_SYNCS);
        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect($this->_getRedirectPath());
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

    protected function _getRedirectPath() {
        return 'adminhtml/reports_reverbreport/index';
    }
}
