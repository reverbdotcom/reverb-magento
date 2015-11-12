<?php
/**
 * Author: Sean Dunagan
 * Created: 9/4/15
 */

class Reverb_Payment_Model_Method_Reverb extends Mage_Payment_Model_Method_Abstract
{
    protected $_isInitializeNeeded = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;

    protected $_code = 'reverbpayment';

    /*
     * Currently this is not being used as $this->_isInitializeNeeded is set to false. In the event that we want to
     *      prevent the Magento system from notifying the customer of the order, we would need to uncomment this
     *      method and switch $this->_isInitializeNeeded to true
     *
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_NEW);
        $stateObject->setIsNotified(true);
    }
    */

    public function isAvailable($quote = null)
    {
        $transportObject = new Varien_Object();
        $transportObject->setShouldBeAllowed(false);

        Mage::dispatchEvent('should_reverb_payment_be_allowed',
            array('transport_object' => $transportObject, 'quote' => $quote));

        return $transportObject->getShouldBeAllowed();
    }
}
