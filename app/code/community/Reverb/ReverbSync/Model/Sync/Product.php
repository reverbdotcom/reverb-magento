<?php
/**
 * Author: Sean Dunagan
 * Created: 8/14/15
 */

class Reverb_ReverbSync_Model_Sync_Product extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_PRODUCT_ID_INVALID = '%s::%s was passed an invalid product_id %s in its argument object (no sku found for that product id). This method is intended to be used as a callback for a Reverb_ProcessQueue_Model_Task object. The serialized arguments object was %s.';
    const EXCEPTION_SYNCING_PRODUCT = 'Error syncing Reverb product id %s: %s';

    /**
     * Method intended to be used as a Reverb_ProcessQueue_Model_Task callback.
     *
     * @param $argumentsObject - Expected to be of type Varien_Object and have field 'product_id' set in its $_data
     *                              instance field array
     * @return Reverb_ProcessQueue_Model_Task_Result_Interface
     */
    public function executeQueuedIndividualProductDataSync(stdClass $argumentsObject)
    {
        $taskExecutionResult = Mage::getModel('reverb_process_queue/task_result');
        /* @var $taskExecutionResult Reverb_ProcessQueue_Model_Task_Result */

        $product_id = $argumentsObject->product_id;
        $product_sku = Mage::getResourceSingleton('reverbSync/catalog_product')->getSkuById($product_id);

        if (empty($product_sku))
        {
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_PRODUCT_ID_INVALID, __CLASS__, __METHOD__, $product_id, serialize($argumentsObject));
            Mage::getSingleton('reverb_process_queue/log')->logQueueProcessorError($error_message);

            $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_ERROR);
            $taskExecutionResult->setTaskStatusMessage($error_message);
            return $taskExecutionResult;
        }

        try
        {
            $listings_wrapper_array = Mage::helper('ReverbSync/sync_product')->executeIndividualProductDataSync($product_id);
        }
        catch(Exception $e)
        {
            $error_message = Mage::helper('ReverbSync')->__(self::EXCEPTION_SYNCING_PRODUCT, $product_id, $e->getMessage());
            Mage::getSingleton('reverb_process_queue/log')->logQueueProcessorError($error_message);

            $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_ERROR);
            $taskExecutionResult->setTaskStatusMessage($error_message);
            return $taskExecutionResult;
        }

        try
        {
            foreach($listings_wrapper_array as $listingWrapper)
            {
                /* @var $listingWrapper Reverb_ReverbSync_Model_Wrapper_Listing */

                // If we have reached this point, and the create/update performed above was successful, and the admin
                //      uploaded any new images, queue image syncs for each of the new images
                if ($listingWrapper->wasCallSuccessful())
                {
                    $product = $listingWrapper->getMagentoProduct();
                    Mage::helper('ReverbSync/sync_image')->queueImageSyncForProductGalleryImages($product);
                }
            }
        }
        catch(Exception $e)
        {
            // Exceptions during image sync should NOT prevent product save
        }

        $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE);
        return $taskExecutionResult;
    }
}
