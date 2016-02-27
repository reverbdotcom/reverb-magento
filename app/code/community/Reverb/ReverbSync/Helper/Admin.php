<?php

class Reverb_ReverbSync_Helper_Admin extends Mage_Core_Helper_Data
{
    protected $_moduleName = 'ReverbSync';

    public function throwRedirectException($error_message, $redirect_path, $redirect_arguments = array())
    {
        $this->addAdminErrorMessage($error_message);
        Mage::log($error_message, null, 'reverb_adminhtml_sync.log');

        $exception = new Reverb_ReverbSync_Model_Exception_Redirect($error_message);
        Mage::logException($exception);
        $exception->prepareRedirect($redirect_path, $redirect_arguments);
        throw $exception;
    }

    public function addAdminSuccessMessage($success_message)
    {
        return Mage::getSingleton('adminhtml/session')->addSuccess($this->__($success_message));
    }

    public function addAdminErrorMessage($error_message)
    {
        return Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));

    }
}
