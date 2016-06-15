<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation extends Reverb_ReverbSync_Helper_Orders_Creation_Helper
{
    const ERROR_AMOUNT_PRODUCT_MISSING = 'The amount_product object, which is supposed contain the product\'s price, was not found';
    const ERROR_AMOUNT_TAX_MISSING = 'The amount_tax object, which is supposed contain the product\'s tax amount, was not found';
    const ERROR_INVALID_SKU = 'An attempt was made to create an order in magento for a Reverb order which had an invalid sku %s';
    const INVALID_CURRENCY_CODE = 'An invalid currency code %s was defined.';
    const EXCEPTION_UPDATE_STORE_NAME = 'An error occurred while setting the store name to %s for order with Reverb Order Id #%s: %s';
    const EXCEPTION_CONFIGURED_STORE_ID = 'An exception occurred while attempting to load the store with the configured store id of %s: %s';
    const EXCEPTION_REVERB_ORDER_CREATION_EVENT_OBSERVER = 'An exception occurred while firing the reverb_order_creation event for order with Reverb Order Number #%s: %s';

    const STORE_TO_SYNC_ORDERS_TO_CONFIG_PATH = 'ReverbSync/orders_sync/store_to_sync_order_to';

    const REVERB_ORDER_STORE_NAME = 'Reverb';

    /**
     * Creates the Reverb Order in the Magento system
     *
     * @param stdClass $reverbOrderObject
     * @return Mage_Sales_Model_Order
     * @throws Exception
     * @throws Reverb_ReverbSync_Model_Exception_Deactivated_Order_Sync
     */
    public function createMagentoOrder(stdClass $reverbOrderObject)
    {
        // Including this check here just to ensure that orders aren't synced if the setting is disabled
        if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
        {
            $exception_message = Mage::helper('ReverbSync/orders_sync')->getOrderSyncIsDisabledMessage();
            throw new Reverb_ReverbSync_Model_Exception_Deactivated_Order_Sync($exception_message);
        }

        $storeId = $this->_getStoreId();
        $quoteToBuild = Mage::getModel('sales/quote')->setStoreId($storeId);
        $reverb_order_number = $reverbOrderObject->order_number;

        if (Mage::helper('ReverbSync/orders_sync')->isOrderSyncSuperModeEnabled())
        {
            // Process this quote as though we were an admin in the admin panel
            $quoteToBuild->setIsSuperMode(true);
        }

        $productToAddToQuote = $this->_getProductToAddToQuote($reverbOrderObject);
        $qty = $reverbOrderObject->quantity;
        if (empty($qty))
        {
            $qty = 1;
        }
        $qty = intval($qty);
        $quoteItem = $quoteToBuild->addProduct($productToAddToQuote, $qty);

        $this->_addReverbItemLinkToQuoteItem($quoteItem, $reverbOrderObject);

        $this->_addTaxAndCurrencyToQuoteItem($quoteToBuild, $reverbOrderObject);

        $this->_getCustomerHelper()->addCustomerToQuote($reverbOrderObject, $quoteToBuild);

        $this->_getAddressHelper()->addOrderAddressAsShippingAndBillingToQuote($reverbOrderObject, $quoteToBuild);
        $this->_getShippingHelper()->setShippingMethodAndRateOnQuote($reverbOrderObject, $quoteToBuild);
        $this->_getPaymentHelper()->setPaymentMethodOnQuote($reverbOrderObject, $quoteToBuild);

        // The calling block will handle catching any exceptions occurring from the calls below
        $service = Mage::getModel('sales/service_quote', $quoteToBuild);
        $service->submitAll();

        $order = $service->getOrder();

        $order->setReverbOrderId($reverb_order_number);

        $reverb_order_status = $reverbOrderObject->status;
        if (empty($reverb_order_status))
        {
            $reverb_order_status = 'created';
        }
        $order->setReverbOrderStatus($reverb_order_status);

        $order->save();

        try
        {
            // Update store name as adapter query for performance consideration purposes
            Mage::getResourceSingleton('reverbSync/order')
                ->setReverbStoreNameByReverbOrderId($reverb_order_number, self::REVERB_ORDER_STORE_NAME);
        }
        catch(Exception $e)
        {
            // Log the exception but don't stop execution
            $error_message = $this->__(self::EXCEPTION_UPDATE_STORE_NAME, self::REVERB_ORDER_STORE_NAME, $reverb_order_number, $e->getMessage());
            $this->_logOrderSyncError($error_message);
        }

        try
        {
            // Dispatch an event for clients to hook in to regarding order creation
            Mage::dispatchEvent('reverb_order_created',
                                array('magento_order_object' => $order, 'reverb_order_object' => $reverbOrderObject)
            );
        }
        catch(Exception $e)
        {
            // Log the exception but don't stop execution
            $error_message = $this->__(self::EXCEPTION_REVERB_ORDER_CREATION_EVENT_OBSERVER, $reverb_order_number,
                                        $e->getMessage());
            $this->_logOrderSyncError($error_message);
        }

        $this->_getShippingHelper()->unsetOrderBeingSynced();
        $this->_getPaymentHelper()->unsetOrderBeingSynced();

        return $order;
    }

    protected function _getProductToAddToQuote(stdClass $reverbOrderObject)
    {
        $sku = $reverbOrderObject->sku;

        $product_entity_id = Mage::getResourceSingleton('catalog/product')->getIdBySku($sku);
        if (empty($product_entity_id))
        {
            $error_message = $this->__(self::ERROR_INVALID_SKU, $sku);
            throw new Exception($error_message);
        }

        $product = Mage::getModel('catalog/product')->load($product_entity_id);
        if ((!is_object($product)) || (!$product->getId()))
        {
            $error_message = $this->__(self::ERROR_INVALID_SKU, $sku);
            throw new Exception($error_message);
        }

        $amountProductObject = $reverbOrderObject->amount_product;
        if (!is_object($amountProductObject))
        {
            $error_message = $this->__(self::ERROR_AMOUNT_PRODUCT_MISSING);
            throw new Exception($error_message);
        }

        $amount = $amountProductObject->amount;
        if (empty($amount))
        {
            $amount = "0.00";
        }
        $product_cost = floatval($amount);
        $product->setPrice($product_cost);

        return $product;
    }

    protected function _addReverbItemLinkToQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem, $reverbOrderObject)
    {
        if (isset($reverbOrderObject->_links->listing->href))
        {
            $listing_api_url_path = $reverbOrderObject->_links->listing->href;
            $quoteItem->setReverbItemLink($listing_api_url_path);
        }
    }

    protected function _addTaxAndCurrencyToQuoteItem(Mage_Sales_Model_Quote $quoteToBuild, $reverbOrderObject)
    {
        if (property_exists($reverbOrderObject, 'amount_tax'))
        {
            $amountTaxObject = $reverbOrderObject->amount_tax;
            if (is_object($amountTaxObject))
            {
                $tax_amount = $amountTaxObject->amount;
                if (empty($tax_amount))
                {
                    $tax_amount = "0.00";
                }
            }
            else
            {
                $tax_amount = "0.00";
            }
        }
        else
        {
            $tax_amount = "0.00";
        }

        $totalBaseTax = floatval($tax_amount);

        $quoteItem = $quoteToBuild->getItemsCollection()->getFirstItem();
        $quoteItem->setBaseTaxAmount($totalBaseTax);
        $totalTax = $quoteToBuild->getStore()->convertPrice($totalBaseTax);
        $quoteItem->setTaxAmount($totalTax);

        // The check to ensure this field is set has already been made at this point
        $amountProductObject = $reverbOrderObject->amount_product;
        $currency_code = $amountProductObject->currency;
        $currencyHelper = Mage::helper('ReverbSync/orders_creation_currency');
        if (!empty($currency_code))
        {
            if (!$currencyHelper->isValidCurrencyCode($currency_code))
            {
                $error_message = $this->__(self::INVALID_CURRENCY_CODE, $currency_code);
                throw new Exception($error_message);
            }
        }
        else
        {
            $currency_code = $currencyHelper->getDefaultCurrencyCode();
        }
        $currencyToForce = Mage::getModel('directory/currency')->load($currency_code);
        $quoteToBuild->setForcedCurrency($currencyToForce);
    }

    protected function _getStoreId()
    {
        // Check to see if the system configured store id is valid
        $system_configured_store_id = $this->_getSystemConfigurationStoreId();
        if ((!is_null($system_configured_store_id)) && ($system_configured_store_id !== false))
        {
            // If so return it
            return $system_configured_store_id;
        }

        // Return the first "real" store Id, falling back to the special Admin store if no stores are defined (unlikely)
        $websites = Mage::app()->getWebsites(true);
        $defaultSite = $adminSite = null;

        foreach($websites as $website) {
            if ($website->getId() == 0) {
                $adminSite = $website;
                continue;
            }
            $defaultSite = $website;
            break;
        }

        $website = !is_null($defaultSite) ? $defaultSite : $adminSite;
        return $website->getDefaultGroup()->getDefaultStoreId();
    }

    protected function _getSystemConfigurationStoreId()
    {
        try
        {
            $configured_store_id = Mage::getStoreConfig(self::STORE_TO_SYNC_ORDERS_TO_CONFIG_PATH);
            if (Mage::getSingleton('reverbSync/source_store')->isAValidStoreId($configured_store_id))
            {
                return $configured_store_id;
            }
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_CONFIGURED_STORE_ID, $configured_store_id, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
        }

        return false;
    }
}
