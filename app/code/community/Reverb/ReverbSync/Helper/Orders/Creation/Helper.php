<?php
/**
 * Author: Sean Dunagan
 * Created: 9/4/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation_Helper extends Mage_Core_Helper_Abstract
{
    protected $_moduleName = 'ReverbSync';

    protected $_shippingHelper = null;
    protected $_paymentHelper = null;
    protected $_addressHelper = null;
    protected $_customerHelper = null;

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

    protected function _getAddressHelper()
    {
        if (is_null($this->_addressHelper))
        {
            $this->_addressHelper = Mage::helper('ReverbSync/orders_creation_address');
        }

        return $this->_addressHelper;
    }

    protected function _getCustomerHelper()
    {
        if (is_null($this->_customerHelper))
        {
            $this->_customerHelper = Mage::helper('ReverbSync/orders_creation_customer');
        }

        return $this->_customerHelper;
    }

    public function getExplodedNameFields($name_as_string)
    {
        $exploded_name = explode(' ', $name_as_string);
        $first_name = array_shift($exploded_name);
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

        return array($first_name, $middle_name, $last_name);
    }

    protected function _logOrderSyncError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
    }
}
