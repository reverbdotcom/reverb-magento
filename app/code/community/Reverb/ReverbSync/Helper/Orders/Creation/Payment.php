<?php
/**
 * Author: Sean Dunagan
 * Created: 9/4/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation_Payment extends Reverb_ReverbSync_Helper_Orders_Creation_Sync
{
    protected $_payment_method_code = 'reverbpayment';

    public function setPaymentMethodOnQuote($reverbOrderObject, $quoteToBuild)
    {
        $this->_setOrderBeingSyncedInRegistry($reverbOrderObject);

        $quoteToBuild->getShippingAddress()->setPaymentMethod($this->_payment_method_code);
        $quoteToBuild->getShippingAddress()->setCollectShippingRates(true);

        $payment = $quoteToBuild->getPayment();
        $payment->importData(array('method' => $this->_payment_method_code));
        $quoteToBuild->save();
        $quoteToBuild->setTotalsCollectedFlag(false);
        $quoteToBuild->collectTotals();
        $quoteToBuild->save();
    }
}
