<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Cron_Orders_Retrieval
    extends Reverb_Process_Model_Locked_File_Cron_Abstract
    implements Reverb_Process_Model_Locked_File_Cron_Interface
{
    const CRON_UNCAUGHT_EXCEPTION = 'An uncaught exception occurred while processing the Reverb Order Sync Process Queue: %s';

    public function executeCron()
    {
        try
        {
            if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
            {
                Mage::helper('ReverbSync/orders_sync')->logOrderSyncDisabledMessage();
                return false;
            }

            Mage::helper('ReverbSync/orders_retrieval_creation')->queueReverbOrderSyncActions();
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::CRON_UNCAUGHT_EXCEPTION, $e->getMessage());
            Mage::log($error_message, null, 'reverb_process_queue_error.log');
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);
        }
    }

    /**
     * We only want one thread to be retrieving orders via the Reverb API
     *
     * @return int
     */
    public function getParallelThreadCount()
    {
        return 1;
    }

    public function getLockFileName()
    {
        return 'reverb_order_retrieval';
    }

    public function getLockFileDirectory()
    {
        return Mage::getBaseDir('var') . DS . 'lock' . DS . 'reverb_order_retrieval';
    }

    public function getCronCode()
    {
        return 'reverb_order_retrieval';
    }
}
