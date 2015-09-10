<?php

class Reverb_ReverbSync_Model_Observer
{
    //function to create the product in reverb
    public function productSave($observer)
    {
        $productSyncHelper = Mage::helper('ReverbSync/sync_product');
        $product_id = $observer->getProduct()->getId();
        try
        {
            $productSyncHelper->executeIndividualProductDataSync($product_id);
        }
        catch(Reverb_ReverbSync_Model_Exception_Product_Excluded $e)
        {
            // If the product has been listed as being excluded from the sync, don't prevent product save
        }
        catch(Reverb_ReverbSync_Model_Exception_Deactivated $e)
        {
            // If the module is deactivated, don't prevent product save
        }
        // Any other Exception is understood to prevent product save
    }

    // function to get the product quantity placed through order
    public function orderSave($observer)
    {
        try
        {
            $productSyncHelper = Mage::helper('ReverbSync/sync_product');
            $order = $observer -> getEvent() -> getOrder();

            foreach ($order->getAllItems() as $item)
            {
                try
                {
                    $product_id = $item->getProductId();
                    $productSyncHelper->executeIndividualProductDataSync($product_id, true);
                }
                catch(Reverb_ReverbSync_Model_Exception_Product_Excluded $e)
                {
                    // If the product has been listed as being excluded from the sync, don't log an exception
                }
                catch(Reverb_ReverbSync_Model_Exception_Deactivated $e)
                {
                    // If the module is deactivated, don't log an exception
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
}
