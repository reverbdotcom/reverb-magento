<?php

class Reverb_ReverbSync_Adminhtml_SyncController extends Mage_Adminhtml_Controller_Action
{
    const BULK_SYNC_EXCEPTION = 'An uncaught exception occurred while executing the Reverb Bulk Product Sync via the admin panel: %s';
    const SUCCESS_BULK_SYNC_COMPLETED = 'Reverb Bulk product sync process completed.';
    const SUCCESS_BULK_SYNC_QUEUED_UP = '%s products have been queued to be synced with Reverb';

    protected $_adminHelper = null;

    public function bulkSyncAction()
    {
        try
        {
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
            $error_message = sprintf(self::BULK_SYNC_EXCEPTION, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message, 'reverbSync/adminhtml_sync/index');
        }

        $success_message = sprintf(self::SUCCESS_BULK_SYNC_QUEUED_UP, $number_of_syncs_queued_up);
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__($success_message));
        $this->_redirect('reverbSync/adminhtml_sync/index');
    }

    public function indexAction()
    {
        $module_helper_groupname = $this->getModuleHelperGroupname();
        $module_description = $this->getControllerDescription();
        $module_block_classname = $this->getModuleBlockGroupname() . '/adminhtml_index';

        $this->loadLayout()
            ->_setActiveMenuValue()
            ->_setSetupTitle(Mage::helper($module_helper_groupname)->__($module_description))
            ->_addBreadcrumb()
            ->_addBreadcrumb(Mage::helper($module_helper_groupname)->__($module_description), Mage::helper($module_helper_groupname)->__($module_description))
            ->_addContent($this->getLayout()->createBlock($module_block_classname))
            ->renderLayout();
    }

    public function getModuleHelperGroupname()
    {
        return "ReverbSync";
    }

    public function getModuleBlockGroupname()
    {
        return "ReverbSync";
    }

    public function getControllerDescription()
    {
        return "Reverb Product Sync";
    }

    protected function _setActiveMenuValue()
    {
        return parent::_setActiveMenu($this->getControllerActiveMenuPath());
    }

    public function getControllerActiveMenuPath()
    {
        return 'catalog/reverb_sync';
    }

    protected function _setSetupTitle($title)
    {
        try
        {
            $this->_title($title);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _getAdminHelper()
    {
        if (is_null($this->_adminHelper))
        {
            $this->_adminHelper = Mage::helper('ReverbSync/admin');
        }

        return $this->_adminHelper;
    }
}
