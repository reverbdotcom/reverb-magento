<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Model_Cron_Orders_Update
    extends Reverb_Process_Model_Locked_File_Cron_Abstract
    implements Reverb_Process_Model_Locked_File_Cron_Interface
{
    const CRON_UNCAUGHT_EXCEPTION = 'An uncaught exception occurred while processing the Reverb Order Update Process Queue: %s';

    public function executeCron()
    {
        try
        {
            if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
            {
                return false;
            }

            Mage::helper('reverb_process_queue/task_processor')->processQueueTasks('order_update');
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::CRON_UNCAUGHT_EXCEPTION, $e->getMessage());
            Mage::log($error_message, null, 'reverb_process_queue_error.log');
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);
        }
    }

    public function getParallelThreadCount()
    {
        return 1;
    }

    public function getLockFileName()
    {
        return 'reverb_order_update';
    }

    public function getLockFileDirectory()
    {
        return Mage::getBaseDir('var') . DS . 'lock' . DS . 'reverb_order_update';
    }

    public function getCronCode()
    {
        return 'reverb_order_update';
    }
}
