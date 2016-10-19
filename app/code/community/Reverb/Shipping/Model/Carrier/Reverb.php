<?php
/**
 * Author: Sean Dunagan
 * Created: 9/3/15
 */

class Reverb_Shipping_Model_Carrier_Reverb extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_code = 'reverbshipping';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|false|Mage_Core_Model_Abstract
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active'))
        {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        /* @var Mage_Shipping_Model_Rate_Result $result */

        $transportObject = $this->_shouldMethodBeAllowed($request);

        if($transportObject->getShouldBeAllowed())
        {
            $method = Mage::getModel('shipping/rate_result_method');
            /* @var Mage_Shipping_Model_Rate_Result_Method $method */

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($transportObject->getShippingPrice());
            $method->setCost($transportObject->getShippingCost());

            $result->append($method);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('reverbshipping' => $this->getConfigData('name'));
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Varien_Object
     */
    protected function _shouldMethodBeAllowed(Mage_Shipping_Model_Rate_Request $request)
    {
        $transportObject = new Varien_Object();
        $transportObject->setShouldBeAllowed(false);
        $transportObject->setShippingPrice(0.00);
        $transportObject->setShippingCost(0.00);

        Mage::dispatchEvent('should_reverb_shipping_be_allowed',
                                array('transport_object' => $transportObject, 'rate_request' => $request));

        return $transportObject;
    }
}
