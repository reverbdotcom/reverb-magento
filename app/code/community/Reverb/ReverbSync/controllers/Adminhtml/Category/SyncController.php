<?php

require_once('Reverb/ReverbSync/controllers/Adminhtml/BaseController.php');
class Reverb_ReverbSync_Adminhtml_Category_SyncController extends Reverb_ReverbSync_Adminhtml_BaseController
{
    const BULK_SYNC_EXCEPTION = 'An uncaught exception occurred while executing the Reverb Bulk Product Sync via the admin panel: %s';
    const SUCCESS_BULK_SYNC_COMPLETED = 'Reverb Bulk product sync process completed.';
    const SUCCESS_BULK_SYNC_QUEUED_UP = '%s products have been queued to be synced with Reverb';
    const EXCEPTION_STOP_BULK_SYNC = 'An exception occurred while attempting to stop all reverb listing sync tasks: %s';
    const SUCCESS_STOPPED_LISTING_SYNCS = 'Stopped all pending Reverb Listing Sync tasks';

    protected $_adminHelper = null;

    public function getUriPathForAction($action)
    {
        $uri_path = sprintf('%s/%s/%s', 'reverbSync', 'category_sync', $action);
        return $uri_path;
    }

    public function getBlockToShow()
    {
        return $this->getModuleBlockGroupname() . '/adminhtml_category_edit';
    }

    public function getControllerDescription()
    {
        return "Reverb Category Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'catalog/reverb_category_sync';
    }

    public function getModuleBlockGroupname()
    {
        return 'ReverbSync';
    }

    public function getObjectParamName()
    {
        return 'reverb_category_map';
    }
}
