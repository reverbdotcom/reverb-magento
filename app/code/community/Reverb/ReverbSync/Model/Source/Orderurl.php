<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Model_Source_Orderurl
{
    const ALL_ORDERS_URL = '/api/my/orders/selling/all?created_start_date=%s';
    const ALL_ORDERS_LABEL = 'All (including Unpaid Accepted Offers)';

    // Note: for awaiting shipment, we are using the updated timestamp because
    // Orders may be created at time X but be awaiting shipment at X+Y.
    //
    // Thus it is safer to use the updated timestamp to make sure we don't miss any that were created
    // before our last sync but changed to awaiting shipment after the last sync.
    const ORDERS_AWAITING_SHIPMENT_URL = '/api/my/orders/selling/awaiting_shipment?updated_start_date=%s';
    const ORDERS_AWAITING_SHIPMENT_LABEL = 'Paid Orders Awaiting Shipment';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::ALL_ORDERS_URL, 'label' => Mage::helper('ReverbSync')->__(self::ALL_ORDERS_LABEL)),
            array('value' => self::ORDERS_AWAITING_SHIPMENT_URL, 'label' => Mage::helper('ReverbSync')->__(self::ORDERS_AWAITING_SHIPMENT_LABEL)),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::ALL_ORDERS_URL => Mage::helper('ReverbSync')->__(self::ALL_ORDERS_LABEL),
            self::ORDERS_AWAITING_SHIPMENT_URL => Mage::helper('ReverbSync')->__(self::ORDERS_AWAITING_SHIPMENT_LABEL),
        );
    }
}
