<?php

abstract class Reverb_Process_Model_Locked_Abstract implements Reverb_Process_Model_Locked_Interface
{
    abstract public function attemptLock();

    abstract public function releaseLock();

    protected function _logError($error_message)
    {
        Mage::log($error_message);
        $exceptionToLog = new Exception($error_message);
        Mage::logException($exceptionToLog);
    }
}
