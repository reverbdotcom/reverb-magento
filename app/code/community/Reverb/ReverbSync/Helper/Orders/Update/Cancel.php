<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 10/28/15
 */

class Reverb_ReverbSync_Helper_Orders_Update_Cancel extends Reverb_ReverbSync_Helper_Orders_Update_Abstract
{
    const ORDER_HAS_ALREADY_BEEN_CANCELLED = 'The Magento order has already been cancelled';

    public function getUpdateAction()
    {
        return 'cancelled';
    }

    /**
     * This method does not catch exceptions as it expects the calling block to catch them by design
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param string                 $reverb_order_status
     *
     * @return Mage_Sales_Model_Order
     * @throws Reverb_ReverbSync_Model_Exception_Order_Update_Status_Redundant
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
            $this->_inspectWhyCanNotUpdateAndThrowException($magentoOrder);
        }
        // Execute the order cancel
        $magentoOrder->cancel()->save();
        // Return the order
        return $magentoOrder;
    }
}
