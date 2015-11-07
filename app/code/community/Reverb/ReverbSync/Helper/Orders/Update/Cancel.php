<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 10/28/15
 */

class Reverb_ReverbSync_Helper_Orders_Update_Cancel extends Mage_Core_Helper_Abstract
{
    const ORDER_HAS_ALREADY_BEEN_CANCELLED = 'The Magento order has already been cancelled';
    const ORDER_CAN_NOT_BE_CANCELLED = 'The Magento order with increment id %s can not be cancelled: %s';

    const REASON_PAYMENT_REVIEW = 'The order is in payment review state';
    const REASON_HOLD = 'The order is in the hold state';
    const REASON_INVOICED = 'The order has already been invoiced';
    const REASON_CANCELLED = 'The order has already been cancelled';
    const REASON_COMPLETE = 'The order has already been marked as complete';
    const REASON_CLOSED = 'The order has already been marked as closed';
    const REASON_DEFAULT = 'The order can not be cancelled';

    /**
     * This method does not catch exceptions as it expects the calling block to catch them by design
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param                        $reverb_order_status
     */
    public function executeMagentoOrderCancel(Mage_Sales_Model_Order $magentoOrder, $reverb_order_status)
    {
        // Check to see if the order has already been cancelled
        if ($magentoOrder->isCanceled())
        {
            throw new Reverb_ReverbSync_Model_Exception_Order_Update_Status_Redundant(self::ORDER_HAS_ALREADY_BEEN_CANCELLED);
        }
        // Check to see if the order can be cancelled
        if (!$magentoOrder->canCancel())
        {
            $this->_inspectWhyCanNotCancelAndThrowException($magentoOrder);
        }
        // Execute the order cancel
        $magentoOrder->cancel()->save();
        // Return the order
        return $magentoOrder;
    }

    protected function _inspectWhyCanNotCancelAndThrowException(Mage_Sales_Model_Order $magentoOrder)
    {
        if ($magentoOrder->isPaymentReview())
        {
            $this->_throwCanNotCancelException($magentoOrder, self::REASON_PAYMENT_REVIEW);
        }
        if ($magentoOrder->canUnhold())
        {
            $this->_throwCanNotCancelException($magentoOrder, self::REASON_HOLD);
        }
        if ($magentoOrder->isCanceled())
        {
            $this->_throwCanNotCancelException($magentoOrder, self::REASON_CANCELLED);
        }

        $state = $magentoOrder->getState();
        if ($state === Mage_Sales_Model_Order::STATE_COMPLETE)
        {
            $this->_throwCanNotCancelException($magentoOrder, self::REASON_COMPLETE);
        }
        if ($state === Mage_Sales_Model_Order::STATE_CLOSED)
        {
            $this->_throwCanNotCancelException($magentoOrder, self::REASON_CLOSED);
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
            $this->_throwCanNotCancelException($magentoOrder, self::REASON_INVOICED);
        }

        $this->_throwCanNotCancelException($magentoOrder, self::REASON_DEFAULT);
    }

    protected function _throwCanNotCancelException(Mage_Sales_Model_Order $magentoOrder, $reason)
    {
        $error_message = $this->__(self::ORDER_CAN_NOT_BE_CANCELLED, $magentoOrder->getIncrementId(), $reason);
        throw new Reverb_ReverbSync_Model_Exception_Data_Order($error_message);
    }
}
