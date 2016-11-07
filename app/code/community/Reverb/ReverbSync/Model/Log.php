<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Log
{
    const LOG_FILE_PREFIX = 'reverb_sync';

    const REVERB_LOGGING_ENABLED_CONFIG_PATH = 'ReverbSync/extensionOption_group/enable_logging';

    /**
     * @var null|bool
     */
    protected $_logging_is_enabled = null;

    public function setSessionErrorIfAdminIsLoggedIn($error_message)
    {
        if (Mage::helper('reverb_base')->isAdminLoggedIn())
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ReverbSync')->__($error_message));
        }
    }

    public function logCategoryMappingError($error_message)
    {
        $this->logSyncError($error_message, 'category_mapping');
    }

    public function logOrderSyncError($error_message)
    {
        $this->logSyncError($error_message, 'orders');
    }

    public function logShipmentTrackingSyncError($error_message)
    {
        $this->logSyncError($error_message, 'shipment_tracking');
    }

    public function logListingsFetchError($error_message)
    {
        $this->logSyncError($error_message, 'listings_fetch');
    }

    public function logListingSyncError($error_message)
    {
        $this->logSyncError($error_message, 'listings');
    }

    public function logListingImageSyncError($error_message)
    {
        $this->logSyncError($error_message, 'listing_images');
    }

    /**
     * @param string $message
     */
    public function logApiRequestMessage($message)
    {
        $log_file = 'reverb_curl_requests.log';
        $this->logReverbMessage($message, $log_file);
    }

    /**
     * Log a Reverb sync error message if logging has been enabled for the Reverb module
     * If not, do nothing
     *
     * @param $error_message
     * @param null $sync_process
     */
    public function logSyncError($error_message, $sync_process = null)
    {
        if (is_null($sync_process))
        {
            $sync_process = 'orders';
        }

        $log_file = self::LOG_FILE_PREFIX . '_' . $sync_process . '.log';
        $this->logReverbMessage($error_message, $log_file);
    }

    public function logReverbMessage($message, $file_to_log_to)
    {
        if (!$this->_isReverbLoggingEnabled())
        {
            // Logging is not enabled for the Reverb module, so do nothing
            return;
        }

        Mage::log($message, null, $file_to_log_to);
    }

    /**
     * @return bool
     */
    protected function _isReverbLoggingEnabled()
    {
        if (is_null($this->_logging_is_enabled))
        {
            $reverb_logging_is_enabled = Mage::getStoreConfig(self::REVERB_LOGGING_ENABLED_CONFIG_PATH);
            $this->_logging_is_enabled = (bool)$reverb_logging_is_enabled;
        }
    
        return $this->_logging_is_enabled;
    }
}
