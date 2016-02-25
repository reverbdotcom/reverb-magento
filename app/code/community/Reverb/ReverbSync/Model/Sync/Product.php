<?php
/**
 * Author: Sean Dunagan
 * Created: 8/14/15
 */

class Reverb_ReverbSync_Model_Sync_Product extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_PRODUCT_ID_INVALID = '%s was passed an invalid product_id %s in its argument object (no sku found for that product id). This method is intended to be used as a callback for a Reverb_ProcessQueue_Model_Task object. The serialized arguments object was %s.';
    const EXCEPTION_SYNCING_PRODUCT = 'An exception occurred while syncing Reverb listing for product with sku %s: %s';
    const EXCEPTION_DURING_IMAGE_SYNC = 'An exception occurred while attempting to execute Reverb image syncs for product with sku %s: %s';

    protected $_processQueueLogSingleton = null;
    protected $_reverbSyncLogSingleton = null;

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
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_PRODUCT_ID_INVALID, __METHOD__,
                                                                $product_id, serialize($argumentsObject));
            $this->_getReverbSyncLogSingleton()->logListingSyncError($error_message);

            $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_ERROR);
            $taskExecutionResult->setTaskStatusMessage($error_message);
            return $taskExecutionResult;
        }

        try
        {
            $reverbSyncProductHelper = Mage::helper('ReverbSync/sync_product');
            /* @var $reverbSyncProductHelper Reverb_ReverbSync_Helper_Sync_Product */
            $listings_wrapper_array = $reverbSyncProductHelper->executeIndividualProductDataSync($product_id);
        }
        catch(Exception $e)
        {
            $error_message = Mage::helper('ReverbSync')->__(self::EXCEPTION_SYNCING_PRODUCT, $product_sku, $e->getMessage());
            $this->_getReverbSyncLogSingleton()->logListingSyncError($error_message);

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
                    $reverbSyncImageHelper = Mage::helper('ReverbSync/sync_image');
                    /* @var $reverbSyncImageHelper Reverb_ReverbSync_Helper_Sync_Image */
                    $reverbSyncImageHelper->queueImageSyncForProductGalleryImages($product);
                }
            }
        }
        catch(Exception $e)
        {
            // Exceptions during image sync do not prevent queue task from being denoted Complete
            $error_message = Mage::helper('ReverbSync')
                                ->__(self::EXCEPTION_DURING_IMAGE_SYNC, $product_sku, $e->getMessage());
            $this->_getProcessQueueLogSingleton()->logQueueProcessorError($error_message);
        }

        $taskExecutionResult->setTaskStatus(Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE);
        return $taskExecutionResult;
    }

    /**
     * @return Reverb_ReverbSync_Model_Log
     */
    protected function _getReverbSyncLogSingleton()
    {
        if (is_null($this->_reverbSyncLogSingleton))
        {
            $this->_reverbSyncLogSingleton = Mage::getSingleton('reverbSync/log');
        }

        return $this->_reverbSyncLogSingleton;
    }

    /**
     * @return Reverb_ProcessQueue_Model_Log
     */
    protected function _getProcessQueueLogSingleton()
    {
        if (is_null($this->_processQueueLogSingleton))
        {
            $this->_processQueueLogSingleton = Mage::getSingleton('reverb_process_queue/log');
        }

        return $this->_processQueueLogSingleton;
    }
}
