<?php
/**
 * Author: Sean Dunagan
 * Created: 8/14/15
 */

class Reverb_ReverbSync_Model_Sync_Product extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_PRODUCT_ID_INVALID = '%s::%s was passed an invalid product_id %s in its argument object (no sku found for that product id). This method is intended to be used as a callback for a Reverb_ProcessQueue_Model_Task object. The serialized arguments object was %s.';
    const EXCEPTION_SYNCING_PRODUCT = 'Exception occurred when attempting to sync product listing with Reverb for product with id %s: %s';

    /**
     * Method intended to be used as a Reverb_ProcessQueue_Model_Task callback.
     *
     * @param $argumentsObject - Expected to be of type Varien_Object and have field 'product_id' set in its $_data
     *                              instance field array
     */
    public function executeQueuedIndividualProductDataSync(stdClass $argumentsObject)
    {
        $taskExecutionResult = Mage::getModel('reverb_process_queue/task_result');


        $product_id = $argumentsObject->product_id;

        $product_sku = Mage::getResourceSingleton('reverbSync/catalog_product')->getSkuById($product_id);

        if (empty($product_sku))
        {
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_PRODUCT_ID_INVALID, $product_id, serialize($argumentsObject));
            Mage::getSingleton('reverb_process_queue/log')->logQueueProcessorError($error_message);

            $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_ERROR);
            return $taskExecutionResult;
        }

        try
        {
            Mage::helper('ReverbSync/sync_product')->executeIndividualProductDataSync($product_id);
        }
        catch(Exception $e)
        {
            $error_message = Mage::helper('ReverbSync')->__(self::EXCEPTION_SYNCING_PRODUCT, $product_id, $e->getMessage());
            Mage::getSingleton('reverb_process_queue/log')->logQueueProcessorError($error_message);

            $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_ERROR);
            return $taskExecutionResult;
        }

        $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE);
        return $taskExecutionResult;
    }
}
