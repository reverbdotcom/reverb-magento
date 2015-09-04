<?php
/**
 * Author: Sean Dunagan
 * Created: 9/3/15
 */

class Reverb_ReverbSync_Model_Observer_Orders
{
    protected $_reverbShippingHelper = null;
    protected $_reverbPaymentHelper = null;

    public function checkForSyncingReverbOrderForShipping($observer)
    {
        $orderBeingSynced = $this->_getReverbShippingHelper()->getOrderBeingSyncedInRegistry();
        if (is_object($orderBeingSynced))
        {
            $transportObject = $observer->getTransportObject();
            $shippingObject = $orderBeingSynced->shipping;
            if (is_object($shippingObject))
            {
                $shipping_amount = $shippingObject->amount;
                $shipping_amount_float = floatval($shipping_amount);
                $transportObject->setData('shipping_price', $shipping_amount_float);
            }

            $transportObject->setData('should_be_allowed', true);
        }
    }

    public function checkForSyncingReverbOrderForPayment($observer)
    {
        $orderBeingSynced = $this->_getReverbPaymentHelper()->getOrderBeingSyncedInRegistry();
        if (is_object($orderBeingSynced))
        {
            $transportObject = $observer->getTransportObject();
            $transportObject->setData('should_be_allowed', true);
        }
    }

    protected function _getReverbShippingHelper()
    {
        if (is_null($this->_reverbShippingHelper))
        {
            $this->_reverbShippingHelper = Mage::helper('ReverbSync/orders_creation_shipping');
        }

        return $this->_reverbShippingHelper;
    }

    protected function _getReverbPaymentHelper()
    {
        if (is_null($this->_reverbPaymentHelper))
        {
            $this->_reverbPaymentHelper = Mage::helper('ReverbSync/orders_creation_payment');
        }

        return $this->_reverbPaymentHelper;
    }
}
