<?php
/**
 * Author: Sean Dunagan
 * Created: 8/14/15
 */

/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Class Reverb_ReverbSync_Model_Cron_Orders_Creation
 * @deprecated
 *
 * As of 2016/05/12, this class is deprecated. Orders are now created via the order update task process
 */
class Reverb_ReverbSync_Model_Cron_Orders_Creation
    extends Reverb_Process_Model_Locked_File_Cron_Abstract
    implements Reverb_Process_Model_Locked_File_Cron_Interface
{
    const CRON_UNCAUGHT_EXCEPTION = 'Error processing the Reverb Order Creation Process Queue: %s';

    public function executeCron()
    {
        try
        {
            if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
            {
                return false;
            }

            Mage::helper('ReverbSync/orders_retrieval_creation')->queueReverbOrderSyncActions();
            Mage::helper('ReverbSync/orders_creation_task_processor')->processQueueTasks('order_creation');
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
        return 'reverb_order_creation';
    }

    public function getLockFileDirectory()
    {
        return Mage::getBaseDir('var') . DS . 'lock' . DS . 'reverb_order_creation';
    }

    public function getCronCode()
    {
        return 'reverb_order_creation';
    }

    protected function _logError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
    }
} 
