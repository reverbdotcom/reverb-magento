<?php
/**
 * Author: Sean Dunagan
 * Created: 9/3/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation_Shipping extends Reverb_ReverbSync_Helper_Orders_Creation_Sync
{
    const ERROR_INVALID_SHIPPING_METHOD_CODE = 'Unable to get a rate for the Reverb Shipping Method';
    protected $_shipping_method_code = 'reverbshipping_reverbshipping';

    public function setShippingMethodAndRateOnQuote($reverbOrderObject, $quoteToBuild)
    {
        $this->_setOrderBeingSyncedInRegistry($reverbOrderObject);

        $shipping_method_code = $this->_shipping_method_code;
        $rate = $quoteToBuild->getShippingAddress()->getShippingRateByCode($shipping_method_code);
        if (!$rate)
        {
            $error_message = $this->__(self::ERROR_INVALID_SHIPPING_METHOD_CODE, $shipping_method_code);
            throw new Exception($error_message);
        }

        $quoteToBuild->getShippingAddress()->setShippingMethod($shipping_method_code);
        $quoteToBuild->setTotalsCollectedFlag(false);
        $quoteToBuild->collectTotals();
        $quoteToBuild->save();
    }

    public function addShippingAddressToQuote($reverbOrderObject, $customerAddress, $quoteToBuild)
    {
        $shippingQuoteAddress = Mage::getModel('sales/quote_address')
                                    ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING);
        $quoteToBuild->addAddress($shippingQuoteAddress);

        $shippingQuoteAddress->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
                    ->setEntityType('customer_address')
                    ->setIsAjaxRequest(false);
        $addressForm->setEntity($shippingQuoteAddress);
        $addressErrors = $addressForm->validateData($shippingQuoteAddress->getData());




        // TODO!!! TEST THIS
        if ($addressErrors !== true)
        {
            $address_errors_message = implode(', ', $addressErrors);
            $serialized_data_array = serialize($shippingQuoteAddress->getData());
            $error_message = sprintf(Reverb_ReverbSync_Helper_Orders_Creation::ERROR_VALIDATING_QUOTE_ADDRESS,
                                        $serialized_data_array, $address_errors_message);
            throw new Exception($error_message);
        }

        $shippingQuoteAddress->implodeStreetAddress();
        $shippingQuoteAddress->setCollectShippingRates(true);

        if (($address_validation = $shippingQuoteAddress->validate()) !== true)
        {
            $serialized_data_array = serialize($shippingQuoteAddress->getData());
            $error_message = sprintf(Reverb_ReverbSync_Helper_Orders_Creation::ERROR_VALIDATING_QUOTE_ADDRESS,
                                        $serialized_data_array, $address_validation);
            throw new Exception($error_message);
        }

        $this->_setOrderBeingSyncedInRegistry($reverbOrderObject);

        $quoteToBuild->setTotalsCollectedFlag(false);
        $quoteToBuild->collectTotals()->save();
    }
}
