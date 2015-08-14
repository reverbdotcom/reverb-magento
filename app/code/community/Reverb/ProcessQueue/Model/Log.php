<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

class Reverb_ProcessQueue_Model_Log
{
    public function logQueueProcessorException(Exception $e)
    {
        Mage::logException($e);
        $this->logQueueProcessorError($e->getMessage());
    }

    public function logQueueProcessorError($error_message)
    {
        Mage::log($error_message, null, 'reverb_queue_processor_error.log');
    }
}
