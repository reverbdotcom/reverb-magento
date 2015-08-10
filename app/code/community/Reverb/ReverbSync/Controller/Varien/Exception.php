<?php

class Reverb_ReverbSync_Controller_Varien_Exception extends Mage_Core_Controller_Varien_Exception
{
    public function prepareRedirect($path, $arguments = array())
    {
        $this->_resultCallback = self::RESULT_REDIRECT;
        $this->_resultCallbackParams = array($path, $arguments);
        return $this;
    }
}
