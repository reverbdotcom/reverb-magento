<?php
/**
 * Author: Sean Dunagan
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/Unique/IndexController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Listings_Image_SyncController
    extends Reverb_ProcessQueue_Adminhtml_ProcessQueue_Unique_IndexController
{
    const CONST_INVALID_TASK_ID = 'An invalid Unique Task id was passed to the Reverb Listings Image Sync Controller: %s';
    const EXCEPTION_ACT_ON_TASK = 'An error occurred while acting on the listings image sync with id %s: %s';
    const NOTICE_TASK_ACTION = 'The attempt to sync image file %s for product %s on Reverb has completed.';

    public function indexAction()
    {
        $module_groupname = $this->getModuleGroupname();
        $module_description = $this->getModuleInstanceDescription();

        $this->loadLayout()
            ->_setActiveMenuValue()
            ->_setSetupTitle(Mage::helper($module_groupname)->__($module_description))
            ->_addBreadcrumb()
            ->_addBreadcrumb(Mage::helper($module_groupname)->__($module_description), Mage::helper($module_groupname)->__($module_description))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_listings_image_unique_index'))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_listings_image_task_unique_index'))
            ->renderLayout();
    }

    public function actOnTaskAction()
    {
        $task_id = $this->getRequest()->getParam($this->getObjectParamName());
        $uniqueQueueTask = Mage::getModel('reverb_process_queue/task_unique')->load($task_id);
        if ((!is_object($uniqueQueueTask)) || (!$uniqueQueueTask->getId()))
        {
            $error_message = $this->__(self::CONST_INVALID_TASK_ID, $task_id);
            $this->_logSyncError($error_message);
            Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));
            $exception = new Reverb_ReverbSync_Controller_Varien_Exception($error_message);
            $exception->prepareRedirect('*/*/index');
            throw $exception;
        }

        try
        {
            Mage::helper('reverb_process_queue/task_processor_unique')->processQueueTask($uniqueQueueTask);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_ACT_ON_TASK, $task_id, $e->getMessage());
            $this->_logSyncError($error_message);
            Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));
            $exception = new Reverb_ReverbSync_Controller_Varien_Exception($error_message);
            $exception->prepareRedirect('*/*/index');
            throw $exception;
        }

        $action_text = $uniqueQueueTask->getActionText();
        $filename = $uniqueQueueTask->getSubjectId();
        $sku = Mage::helper('ReverbSync/sync_image')->getSkuForTask($uniqueQueueTask);
        $notice_message = sprintf(self::NOTICE_TASK_ACTION, $action_text, $filename, $sku);
        Mage::getSingleton('adminhtml/session')->addNotice($this->__($notice_message));
        $this->_redirect('*/*/index');
    }

    protected function _logSyncError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logListingImageSyncError($error_message);
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

    public function getModuleInstanceDescription()
    {
        return 'Reverb Listings Image Sync Tasks';
    }

    public function getObjectParamName()
    {
        return 'task_id';
    }

    public function getObjectDescription()
    {
        return 'Sync Task';
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
