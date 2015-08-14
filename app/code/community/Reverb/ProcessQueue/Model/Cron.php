<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 * Class Reverb_ProcessQueue_Model_Cron
 */

class Reverb_ProcessQueue_Model_Cron
{
    const CRON_UNCAUGHT_EXCEPTION = 'An uncaught exception occurred while processing the Reverb Process Queue: %s';

    public function processQueueTasks()
    {
        try
        {
            Mage::helper('reverb_process_queue/task_processor')->processQueueTasks();
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
