<?php
/**
 * Author: Sean Dunagan
 * Created: 9/4/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation_Address extends Reverb_ReverbSync_Helper_Orders_Creation_Helper
{
    const ERROR_NO_ADDRESS = 'An attempt was made to create an order in magento for a Reverb order which did not have an address listed';
    const ERROR_VALIDATING_QUOTE_ADDRESS = "While validating a quote address for a Reverb Order Sync, the address failed validation. The address's serialized data was: %s. The error message was: %s";

    public function addOrderAddressAsShippingAndBillingToQuote(stdClass $reverbOrderObject,
                                                                   Mage_Sales_Model_Quote $quoteToBuild)
    {
        $shippingAddressObject = $reverbOrderObject->shipping_address;
        if (!is_object($shippingAddressObject))
        {
            $error_message = $this->__(self::ERROR_NO_ADDRESS);
            throw new Exception($error_message);
        }

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
        if ($addressErrors !== true)
        {
            $address_errors_message = implode(', ', $addressErrors);
            $serialized_data_array = serialize($billingQuoteAddress->getData());
            $error_message = sprintf(self::ERROR_VALIDATING_QUOTE_ADDRESS, $serialized_data_array, $address_errors_message);
            throw new Exception($error_message);
        }

        if (($address_validation_errors_array = $billingQuoteAddress->validate()) !== true)
        {
            $address_errors_message = implode(', ', $address_validation_errors_array);
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

        list($first_name, $middle_name, $last_name) = $this->getExplodedNameFields($name);

        $street_address = $shippingAddressObject->street_address;
        $extended_address = $shippingAddressObject->extended_address;
        $street_array = array($street_address, $extended_address);

        $region = $shippingAddressObject->region;
        $country_code = $shippingAddressObject->country_code;
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
} 