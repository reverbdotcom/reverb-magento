<?php
/**
 * Author: Sean Dunagan
 * Created: 8/14/15
 */

class Reverb_ReverbSync_Model_Cron
{
    const CRON_UNCAUGHT_EXCEPTION = 'An uncaught exception occurred while processing the Reverb Listing Sync Process Queue: %s';

    public function processListingSyncQueueTasks()
    {
        try
        {
            Mage::helper('reverb_process_queue/task_processor')->processQueueTasks('listing_sync');
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::CRON_UNCAUGHT_EXCEPTION, $e->getMessage());
            Mage::log($error_message, null, 'reverb_process_queue_error.log');
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);
        }
    }
} 