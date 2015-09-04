<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation extends Mage_Core_Helper_Abstract
{
    const ERROR_AMOUNT_PRODUCT_MISSING = 'The amount_product object, which is supposed contain the product\'s price, was not found';
    const ERROR_AMOUNT_TAX_MISSING = 'The amount_tax object, which is supposed contain the product\'s tax amount, was not found';
    const ERROR_INVALID_SKU = 'An attempt was made to create an order in magento for a Reverb order which had an invalid sku %s';
    const ERROR_NO_ADDRESS = 'An attempt was made to create an order in magento for a Reverb order which did not have an address listed';
    const ERROR_VALIDATING_QUOTE_ADDRESS = "While validating a quote address for a Reverb Order Sync, the address failed validation. The address's serialized data was: %s. The error message was: %s";
    const INVALID_CURRENCY_CODE = 'An invalid currency code %s was defined.';
    const ERROR_NO_SHIPPING_PROVIDER_CODE = 'No shipping provider code was declared';

    protected $_moduleName = 'ReverbSync';
    protected $_shippingHelper = null;
    protected $_paymentHelper = null;

    public function createMagentoOrder(stdClass $reverbOrderObject)
    {
        $quoteToBuild = Mage::getModel('sales/quote');

        $productToAddToQuote = $this->_getProductToAddToQuote($reverbOrderObject);
        $qty = $reverbOrderObject->quantity;
        if (empty($qty))
        {
            $qty = 1;
        }
        $qty = intval($qty);
        $quoteToBuild->addProduct($productToAddToQuote, $qty);

        $this->_addTaxAndCurrencyToQuoteItem($quoteToBuild, $reverbOrderObject);

        $magentoCustomerObject = Mage::getModel('customer/customer');
        $quoteToBuild->setCustomer($magentoCustomerObject);

        $this->_addOrderAddressAsShippingAndBillingToQuote($reverbOrderObject, $quoteToBuild);

        $this->_getShippingHelper()->setShippingMethodAndRateOnQuote($reverbOrderObject, $quoteToBuild);
        $this->_getPaymentHelper()->setPaymentMethodOnQuote($reverbOrderObject, $quoteToBuild);

        // The calling block will handle catching any exceptions occurring from the calls below
        $service = Mage::getModel('sales/service_quote', $quoteToBuild);
        $service->submitAll();

        $order = $service->getOrder();

        $reverb_order_number = $reverbOrderObject->order_number;
        $order->setReverbOrderId($reverb_order_number);
        $order->save();

        $this->_getShippingHelper()->unsetOrderBeingSynced();
        $this->_getPaymentHelper()->unsetOrderBeingSynced();
    }

    protected function _getProductToAddToQuote(stdClass $reverbOrderObject)
    {
        $sku = $reverbOrderObject->sku;

        $product_entity_id = Mage::getResourceSingleton('catalog/product')->getIdBySku($sku);



        // TODO!!! TEST THIS
        if (empty($product_entity_id))
        {
            $error_message = $this->__(self::ERROR_INVALID_SKU, $sku);
            throw new Exception($error_message);
        }

        $product = Mage::getModel('catalog/product')->load($product_entity_id);


        // TODO!!! TEST THIS
        if ((!is_object($product)) || (!$product->getId()))
        {
            $error_message = $this->__(self::ERROR_INVALID_SKU, $sku);
            throw new Exception($error_message);
        }

        $amountProductObject = $reverbOrderObject->amount_product;



        // TODO!!! TEST THIS
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
    protected function _addTaxAndCurrencyToQuoteItem(Mage_Sales_Model_Quote $quoteToBuild, $reverbOrderObject)
    {
        $amountTaxObject = $reverbOrderObject->amount_tax;
        if (!is_object($amountTaxObject))
        {
            $error_message = $this->__(self::ERROR_AMOUNT_TAX_MISSING);
            throw new Exception($error_message);
        }

        $tax_amount = $amountTaxObject->amount;
        if (empty($tax_amount))
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

        // TODO!!! TEST THIS
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
    protected function _addOrderAddressAsShippingAndBillingToQuote(stdClass $reverbOrderObject,
                                                                Mage_Sales_Model_Quote $quoteToBuild)
    {
        $shippingAddressObject = $reverbOrderObject->shipping_address;
        if (!is_object($shippingAddressObject))
        {
            $error_message = $this->__(self::ERROR_NO_ADDRESS);
            throw new Exception($error_message);
        }

        //TODO!!!! Check that customer data is set on the quote/order such as name
        $customerAddress = $this->_getCustomerAddressForOrder($shippingAddressObject);

        $this->_addBillingAddressToQuote($customerAddress, $quoteToBuild);
        $this->_getShippingHelper()->addShippingAddressToQuote($reverbOrderObject, $customerAddress, $quoteToBuild);
    }



    protected function _addBillingAddressToQuote($customerAddress, $quoteToBuild)
    {
        $billingQuoteAddress = Mage::getModel('sales/quote_address')
                                    ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING);
        $quoteToBuild->addAddress($billingQuoteAddress);
        $billingQuoteAddress->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
                    ->setEntityType('customer_address')
                    ->setIsAjaxRequest(false);

        $addressForm->setEntity($billingQuoteAddress);
        $addressErrors = $addressForm->validateData($billingQuoteAddress->getData());


        // TODO!!! TEST THIS
        if ($addressErrors !== true)
        {
            $address_errors_message = implode(', ', $addressErrors);
            $serialized_data_array = serialize($billingQuoteAddress->getData());
            $error_message = sprintf(self::ERROR_VALIDATING_QUOTE_ADDRESS, $serialized_data_array, $address_errors_message);
            throw new Exception($error_message);
        }



        // TODO!!! TEST THIS
        if (($address_validation = $billingQuoteAddress->validate()) !== true)
        {
            $address_errors_message = implode(', ', $address_validation);
            $serialized_data_array = serialize($billingQuoteAddress->getData());
            $error_message = sprintf(self::ERROR_VALIDATING_QUOTE_ADDRESS, $serialized_data_array, $address_errors_message);
            throw new Exception($error_message);
        }

        $billingQuoteAddress->implodeStreetAddress();

        $quoteToBuild->collectTotals();
        $quoteToBuild->save();
    }

    protected function _getCustomerAddressForOrder(stdClass $shippingAddressObject)
    {
        $name = $shippingAddressObject->name;
        $exploded_name = explode(' ', $name);
        $first_name = array_shift($exploded_name);


        // TODO!!! Test these cases
        if (empty($exploded_name))
        {
            // Only one word was provided in the name field, default last name to "Customer"
            $last_name = "Customer";
            $middle_name = '';
        }
        else if (count($exploded_name) > 1)
        {
            // Middle name was provided
            $middle_name = array_shift($exploded_name);
            $last_name = implode(' ', $exploded_name);
        }
        else
        {
            $middle_name = '';
            $last_name = implode(' ', $exploded_name);
        }

        $street_address = $shippingAddressObject->street_address;
        $extended_address = $shippingAddressObject->extended_address;
        $street_array = array($street_address, $extended_address);

        $region = $shippingAddressObject->region;
        $country_code = $shippingAddressObject->country_code;

        // TODO!!! Test if this is null
        $regionObject = Mage::getModel('directory/region')->loadByCode($region, $country_code);
        $region_id = $regionObject->getId();

        $address_data_array = array(
            'firstname' => $first_name,
            'middlename' => $middle_name,
            'lastname' => $last_name,
            'street' => $street_array,
            'city' => $shippingAddressObject->locality,
            'country_id' => $shippingAddressObject->country_code,
            'region' => $shippingAddressObject->region,
            'region_id' => $region_id,
            'postcode' => $shippingAddressObject->postal_code,
            'telephone' => $shippingAddressObject->phone,
        );

        $customerAddress = Mage::getModel('customer/address');
        $customerAddress->addData($address_data_array);

        return $customerAddress;
    }

    protected function _getShippingHelper()
    {
        if (is_null($this->_shippingHelper))
        {
            $this->_shippingHelper = Mage::helper('ReverbSync/orders_creation_shipping');
        }

        return $this->_shippingHelper;
    }

    protected function _getPaymentHelper()
    {
        if (is_null($this->_paymentHelper))
        {
            $this->_paymentHelper = Mage::helper('ReverbSync/orders_creation_payment');
        }

        return $this->_paymentHelper;
    }
}
