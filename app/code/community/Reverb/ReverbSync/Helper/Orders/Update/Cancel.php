<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 10/28/15
 */

class Reverb_ReverbSync_Helper_Orders_Update_Cancel extends Mage_Core_Helper_Abstract
{
    const ORDER_HAS_ALREADY_BEEN_CANCELLED = 'The Magento order has already been cancelled';
    const ORDER_CAN_NOT_BE_CANCELLED = 'The Magento system has determined that the Magento order with increment id %s can not be cancelled';

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
            $error_message = $this->__(self::ORDER_CAN_NOT_BE_CANCELLED, $magentoOrder->getIncrementId());
            throw new Reverb_ReverbSync_Model_Exception_Data_Order($error_message);
        }
        // Execute the order cancel
        $magentoOrder->cancel()->save();
        // Return the order
        return $magentoOrder;
    }
}
