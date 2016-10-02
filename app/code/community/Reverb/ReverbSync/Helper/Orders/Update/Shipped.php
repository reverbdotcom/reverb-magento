<?php
/**
 * Author: Sean Dunagan (github: dunagan5887)
 * Date: 10/2/16
 */

/**
 * Class Reverb_ReverbSync_Helper_Orders_Update_Shipped
 */
class Reverb_ReverbSync_Helper_Orders_Update_Shipped extends Reverb_ReverbSync_Helper_Orders_Update_Abstract
{
    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param string $reverb_order_status
     * @param stdClass $orderUpdateArgumentsObject
     */
    public function executeMagentoOrderShipped($magentoOrder, $reverb_order_status, $orderUpdateArgumentsObject)
    {
        // Check to see if this order's shipping address will need to be updated
        $potentiallyUpdatedOrderAddress
            = $this->updateAndReturnOrderShippingAddressIfNecessary($magentoOrder, $orderUpdateArgumentsObject);
        if (!is_null($potentiallyUpdatedOrderAddress))
        {
            $potentiallyUpdatedOrderAddress->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateAction()
    {
        return 'shipped';
    }
}
