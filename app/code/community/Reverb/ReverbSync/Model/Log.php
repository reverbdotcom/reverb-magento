<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Log
{
    const LOG_FILE_PREFIX = 'reverb_sync';

    public function logOrderSyncError($error_message)
    {
        $this->logSyncError($error_message, 'orders');
    }

    public function logSyncError($error_message, $sync_process = null)
    {
        if (is_null($sync_process))
        {
            $sync_process = 'orders';
        }

        $log_file = self::LOG_FILE_PREFIX . '_' . $sync_process . '.log';
        Mage::log($error_message, null, $log_file);
    }
}
