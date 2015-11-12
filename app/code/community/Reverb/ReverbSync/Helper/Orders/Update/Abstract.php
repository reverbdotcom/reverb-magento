<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 11/7/15
 */

abstract class Reverb_ReverbSync_Helper_Orders_Update_Abstract extends Mage_Core_Helper_Abstract
{
    const ORDER_CAN_NOT_BE_UPDATED = 'The Magento order with increment id %s can not be %s: %s';

    const REASON_PAYMENT_REVIEW = 'The order is in payment review state';
    const REASON_HOLD = 'The order is in the hold state';
    const REASON_INVOICED = 'The order has already been invoiced';
    const REASON_CANCELLED = 'The order has already been cancelled';
    const REASON_COMPLETE = 'The order has already been marked as complete';
    const REASON_CLOSED = 'The order has already been marked as closed';
    const REASON_DEFAULT = 'The order can not be cancelled';
    
    abstract public function getUpdateAction();

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param string                 $reason
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order_Update
     */
    protected function _throwCanNotUpdateException(Mage_Sales_Model_Order $magentoOrder, $reason)
    {
        $error_message = $this->__(self::ORDER_CAN_NOT_BE_UPDATED, $magentoOrder->getIncrementId(),
                                   $this->getUpdateAction(), $reason);
        throw new Reverb_ReverbSync_Model_Exception_Data_Order_Update($error_message);
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order_Update
     */
    protected function _inspectWhyCanNotUpdateAndThrowException(Mage_Sales_Model_Order $magentoOrder)
    {
        if ($magentoOrder->isPaymentReview())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_PAYMENT_REVIEW);
        }
        if ($magentoOrder->canUnhold())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_HOLD);
        }
        if ($magentoOrder->isCanceled())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_CANCELLED);
        }

        $state = $magentoOrder->getState();
        if ($state === Mage_Sales_Model_Order::STATE_COMPLETE)
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_COMPLETE);
        }
        if ($state === Mage_Sales_Model_Order::STATE_CLOSED)
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_CLOSED);
        }

        $allInvoiced = true;
        foreach ($magentoOrder->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced)
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_INVOICED);
        }

        $this->_throwCanNotUpdateException($magentoOrder, self::REASON_DEFAULT);
    }
}
