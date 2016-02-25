<?php

class Reverb_ReverbSync_Model_Observer
{
    const EXCEPTION_MASS_ATTRIBUTE_PRODUCT_SYNC = 'An exception occurred while queueing up product listing syncs after a mass product attribute update: %s';
    const EXCEPTION_LISTING_SYNC_ON_ORDER_PLACEMENT = 'An exception was thrown when attempting to queue a background Reverb listing sync task on order placement for product with id %s: %s';
    const EXCEPTION_LISTING_SYNC_ON_PRODUCT_SAVE = 'An exception occurred while attempting to queue a background Reverb listing sync task on product save for product with id %s: %s';

    protected $_logSingleton = null;

    //function to create the product in reverb
    public function productSave($observer)
    {
        $product = $observer->getProduct();
        $product_id = $product->getId();

        $reverbSyncTaskProcessor = Mage::helper('ReverbSync/task_processor');
        /* @var $reverbSyncTaskProcessor Reverb_ReverbSync_Helper_Task_Processor */

        try
        {
            $reverbSyncTaskProcessor->queueListingsSyncByProductIds(array($product_id));
        }
        catch(Exception $e)
        {
            $error_message = $reverbSyncTaskProcessor->__(self::EXCEPTION_LISTING_SYNC_ON_PRODUCT_SAVE,
                                                            $product_id, $e->getMessage());

            $this->_getLogSingleton()->logListingSyncError($error_message);
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);
        }
    }

    // function to get the product quantity placed through order
    public function orderSave($observer)
    {
        try
        {
            $order = $observer->getEvent()->getOrder();

            $reverbSyncTaskProcessor = Mage::helper('ReverbSync/task_processor');
            /* @var $reverbSyncTaskProcessor Reverb_ReverbSync_Helper_Task_Processor */

            foreach ($order->getAllItems() as $item)
            {
                try
                {
                    if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                    {
                        $product_id = $item->getProductId();
                        $reverbSyncTaskProcessor->queueListingsSyncByProductIds(array($product_id));
                    }
                }
                catch(Exception $e)
                {
                    $product_id = $item->getProductId();
                    $error_message = $reverbSyncTaskProcessor->__(self::EXCEPTION_LISTING_SYNC_ON_ORDER_PLACEMENT,
                                                                    $product_id, $e->getMessage());

                    $this->_getLogSingleton()->logListingSyncError($error_message);
                    $exceptionToLog = new Exception($error_message);
                    Mage::logException($exceptionToLog);
                }
            }
        }
        catch(Exception $e)
        {
            Mage::logException($e);
        }
    }

    public function triggerProductSyncOffMassProductUpdate($observer)
    {
        try
        {
            $controllerAction = $observer->getData('controller_action');
            /* @var $controllerAction Mage_Adminhtml_Catalog_Product_Action_AttributeController */
            $inventory_data      = $controllerAction->getRequest()->getParam('inventory', array());
            $attributes_data     = $controllerAction->getRequest()->getParam('attributes', array());

            $listingsUpdateHelper = Mage::helper('ReverbSync/sync_listings_update');
            /* @var $listingsUpdateHelper Reverb_ReverbSync_Helper_Sync_Listings_Update */
            if ($listingsUpdateHelper->shouldMassProductUpdateTriggerProductListingsSync($attributes_data, $inventory_data))
            {
                $catalogProductEditHelper = Mage::helper('adminhtml/catalog_product_edit_action_attribute');
                /* @var $catalogProductEditHelper Mage_Adminhtml_Helper_Catalog_Product_Edit_Action_Attribute */
                $product_ids_to_sync = $catalogProductEditHelper->getProductIds();
                $productSyncHelper = Mage::helper('ReverbSync/sync_product');
                /* @var $productSyncHelper Reverb_ReverbSync_Helper_Sync_Product */
                $number_of_syncs_queued_up = $productSyncHelper->queueUpProductDataSync($product_ids_to_sync);
            }
        }
        catch(Reverb_ReverbSync_Model_Exception_Deactivated $deactivatedException)
        {
            // Do nothing in this event
        }
        catch(Exception $e)
        {
            $error_message = Mage::helper('ReverbSync')->__(self::EXCEPTION_MASS_ATTRIBUTE_PRODUCT_SYNC, $e->getMessage());
            $this->_getLogSingleton()->logListingSyncError($error_message);
        }
    }

    /**
     * @return Reverb_ReverbSync_Model_Log
     */
    protected function _getLogSingleton()
    {
        if (is_null($this->_logSingleton))
        {
            $this->_logSingleton = Mage::getSingleton('reverbSync/log');
        }

        return $this->_logSingleton;
    }
}
