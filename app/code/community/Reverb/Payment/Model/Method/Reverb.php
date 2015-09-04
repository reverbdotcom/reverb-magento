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
     * Currently this is not being used as $this->_isInitializeNeeded is set to false. This is done so that the order
     *      is invoiced automatically during order sync
     *
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
        $stateObject->setIsNotified(true);
    }
    */

    public function capture(Varien_Object $payment, $amount)
    {
        $to_return = parent::capture($payment, $amount);

        $payment->getOrder()->setCustomerNoteNotify(true);

        return $to_return;
    }

    public function isAvailable($quote = null)
    {
        $transportObject = new Varien_Object();
        $transportObject->setShouldBeAllowed(false);

        Mage::dispatchEvent('should_reverb_payment_be_allowed',
            array('transport_object' => $transportObject, 'quote' => $quote));

        return $transportObject->getShouldBeAllowed();
    }
}
