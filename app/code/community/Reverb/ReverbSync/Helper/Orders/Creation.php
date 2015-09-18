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

    public function createMagentoOrder(stdClass $reverbOrderObject)
    {
        // Including this check here just to ensure that orders aren't synced if the setting is disabled
        if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
        {
            $exception_message = Mage::helper('ReverbSync/orders_sync')->getOrderSyncIsDisabledMessage();
            throw new Reverb_ReverbSync_Model_Exception_Deactivated_Order_Sync($exception_message);
        }

        $quoteToBuild = Mage::getModel('sales/quote');
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
        $quoteToBuild->addProduct($productToAddToQuote, $qty);

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

        $this->_getShippingHelper()->unsetOrderBeingSynced();
        $this->_getPaymentHelper()->unsetOrderBeingSynced();
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
}
