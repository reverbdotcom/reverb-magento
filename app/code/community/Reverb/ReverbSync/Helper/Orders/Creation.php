<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation extends Mage_Core_Helper_Abstract
{
    const ERROR_INVALID_SKU = 'An attempt was made to create an order in magento for a Reverb order which had an invalid sku %s';
    const ERROR_NO_ADDRESS = 'An attempt was made to create an order in magento for a Reverb order which did not have an address listed';

    protected $_moduleName = 'ReverbSync';

    public function createMagentoOrder(stdClass $reverbOrderObject)
    {
        $reverb_order_number = $argumentsObject->order_number;


        $productToAddToQuote = $this->_getProductToAddToQuote($reverbOrderObject);

        $shippingAddressAsBilling = $this->_getOrderAddressAsShippingAndBilling($reverbOrderObject);

        $quoteToBuild = Mage::getModel('sales/quote');

        $quoteToBuild->addProduct($productToAddToQuote);
        // #GUESTSUBSCRIPTION
        $quoteToBuild->setCustomer($customerToAddToQuote);

        $addressRecurHelper->addBillingAddressToQuote($quoteToBuild, $shippingAddressAsBilling, $customerToAddToQuote);
        $addressRecurHelper->addShippingAddressAndMethodToQuote($quoteToBuild, $shippingAddressAsBilling,
                                                                $invoiceCreatedLineObject, $createdInvoiceMetaDataObject);

        Mage::helper('petbrosia_stripe/subscription_recur_payment')
            ->setPaymentOnQuote($quoteToBuild, $invoiceCreatedLineObject, $customerToAddToQuote);

        $service = Mage::getModel('sales/service_quote', $quoteToBuild);
        $service->submitAll();

        $order = $service->getOrder();

        try
        {
            $order->setReverbOrderId($reverb_order_number);
            $order->save();
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_OCCURRED_WHILE_TRYING_TO_DENOTE_ORDER_AS_PROGRAMMATIC, $order->getIncrementId(), $e->getMessage());
            Mage::log($error_message, null, 'stripe_subscription_recurrence_error.log');
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);
        }
    }

    /**
     * @param stdClass $reverbOrderObject
     * @throws Exception
     *
     *
     *
     *
     * MUST DO THIS
     *
     *
     *
     *
     */
    protected function _getProductToAddToQuote(stdClass $reverbOrderObject)
    {
        $sku = $reverbOrderObject->sku;

        $product_entity_id = Mage::getResourceSingleton('catalog/product')->getIdBySku($sku);
        if (empty($product_entity_id))
        {
            $error_message = $this->__(self::ERROR_INVALID_SKU, $sku);
            throw new Exception($error_message);
        }


    }

    protected function _getOrderAddressAsShippingAndBilling(stdClass $reverbOrderObject)
    {
        $shippingAddressObject = $reverbOrderObject->shipping_address;
        if (!is_object($shippingAddressObject))
        {
            $error_message = $this->__(self::ERROR_NO_ADDRESS);
            throw new Exception($error_message);
        }

        $quoteAddress = Mage::getModel('sales/quote_address');

        $name = $shippingAddressObject->name;
        $street_address = $shippingAddressObject->street_address;
        $extended_address = $shippingAddressObject->extended_address;
        $locality = $shippingAddressObject->locality;
        $region = $shippingAddressObject->region;
        $postal_code = $shippingAddressObject->postal_code;
        $country_code = $shippingAddressObject->country_code;
        $phone = $shippingAddressObject->phone;
    }
}
