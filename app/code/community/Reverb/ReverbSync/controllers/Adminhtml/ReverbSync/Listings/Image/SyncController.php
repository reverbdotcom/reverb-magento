<?php
/**
 * Author: Sean Dunagan
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/Unique/IndexController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Listings_Image_SyncController
    extends Reverb_ProcessQueue_Adminhtml_ProcessQueue_Unique_IndexController
{
    const NOTICE_TASK_ACTION = 'The attempt to sync image file %s for product %s on Reverb has completed.';

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_listings_image_unique_index'))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_listings_image_task_unique_index'))
            ->renderLayout();
    }

    public function canAdminUpdateStatus()
    {
        return Mage::helper('ReverbSync/sync_image')->canAdminChangeListingsSyncStatus();
    }

    public function getEditBlockClassname()
    {
        return 'ReverbSync/adminhtml_listings_image_task_unique_edit';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_listings_image_task_unique_index';
    }

    public function getControllerDescription()
    {
        return "Reverb Listings Image Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'reverb/reverb_listings_image_sync';
    }

    public function getObjectParamName()
    {
      return 'task_id';
    }

    public function getModuleInstanceDescription()
    {
        return 'Reverb Listings Image Sync Tasks';
    }

    public function getIndexActionsController()
    {
        return 'ReverbSync_listings_image_sync';
    }

    public function getBlockModuleGroupname()
    {
        return $this->_getModuleBlockGroupname();
    }

    protected function _getModuleBlockGroupname()
    {
        return 'ReverbSync';
    }
}
