<?php
/**
 * Author: Sean Dunagan
 * Created: 9/23/15
 */

interface Reverb_ReverbSync_Helper_Api_Adapter_Interface
{
    /**
     * A string description of the API call. It will be used to specify which file to log the curl requests to
     *
     * @return mixed
     */
    public function getApiLogFileSuffix();

    /**
     * This method should return the exception object specific to an API call if one exists. Otherwise, it should return
     *  an object of type Reverb_ReverbSync_Model_Exception_Api
     *
     * @param string $error_message - Exception message
     * @return Reverb_ReverbSync_Model_Exception_Api
     */
    public function getExceptionObject($error_message);

    /**
     * Used to allow for logging errors to specific files. By default, it should call
     *      Mage::getSingleton('reverbSync/log')->logSyncError($error_message);
     *      if no specific log file exists for a specific API call
     *
     * @param $error_message
     */
    public function logError($error_message);
}