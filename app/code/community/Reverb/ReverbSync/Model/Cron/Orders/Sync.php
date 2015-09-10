<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Model_Cron_Orders_Sync
{
    public function executeOrderRetrievalAndProcessing()
    {
        Mage::getModel('reverbSync/cron_orders_retrieval')->attemptCronExecution();
        Mage::getModel('reverbSync/cron_orders_creation')->attemptCronExecution();
    }
} 