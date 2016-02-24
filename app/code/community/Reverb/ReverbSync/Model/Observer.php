<?php

class Reverb_ReverbSync_Model_Observer
{
    const ERROR_MASS_ATTRIBUTE_PRODUCT_SYNC = 'An exception occurred while queueing up product listing syncs after a mass product attribute update: %s';

    protected $_logSingleton = null;

    //function to create the product in reverb
    public function productSave($observer)
    {
        try
        {
            $product = $observer->getProduct();
            $product_id = $product->getId();

            $reverbSyncTaskProcessor = Mage::helper('ReverbSync/task_processor');
            /* @var $reverbSyncTaskProcessor Reverb_ReverbSync_Helper_Task_Processor */
            $reverbSyncTaskProcessor->queueListingsSyncByProductIds(array($product_id));
        }
        catch(Exception $e)
        {
            // Any other Exception is understood to prevent product save
            throw $e;
        }
    }

    // function to get the product quantity placed through order
    public function orderSave($observer)
    {
        try
        {
            $productSyncHelper = Mage::helper('ReverbSync/sync_product');
            $order = $observer -> getEvent() -> getOrder();

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
                catch(Reverb_ReverbSync_Model_Exception_Product_Excluded $e)
                {
                    // If the product has been listed as being excluded from the sync, don't log an exception
                    $this->_getLogSingleton()->setSessionErrorIfAdminIsLoggedIn($e->getMessage());
                }
                catch(Reverb_ReverbSync_Model_Exception_Deactivated $e)
                {
                    // If the module is deactivated, don't log an exception
                    $this->_getLogSingleton()->setSessionErrorIfAdminIsLoggedIn($e->getMessage());
                }
                catch(Exception $e)
                {
                    Mage::logException($e);
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
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_MASS_ATTRIBUTE_PRODUCT_SYNC, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logListingSyncError($error_message);
        }
    }

    protected function _getLogSingleton()
    {
        if (is_null($this->_logSingleton))
        {
            $this->_logSingleton = Mage::getSingleton('reverbSync/log');
        }

        return $this->_logSingleton;
    }
}
