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

    const SOURCE_ORDER_URL_CONFIG = 'ReverbSync/orders_sync/order_sync_reverb_source_url';

    /**
     * @var null|bool
     */
    protected $_only_sync_paid_orders = null;

    /**
     * Returns whether the client has configured the order creation sync process to only sync orders which are awaiting
     *  shipment
     *
     * @return bool
     */
    public function shouldOnlySyncPaidOrders()
    {
        if (is_null($this->_only_sync_paid_orders))
        {
            $order_sync_source_url_configuration_setting = Mage::getStoreConfig(self::SOURCE_ORDER_URL_CONFIG);
            // trim the field just to be safe
            $order_sync_source_url_configuration_setting = trim($order_sync_source_url_configuration_setting);
            // Compare the field to the constant above
            if (!strcmp($order_sync_source_url_configuration_setting, self::ALL_ORDERS_URL))
            {
                $this->_only_sync_paid_orders = false;
            }
            else
            {
                $this->_only_sync_paid_orders = true;
            }
        }

        return $this->_only_sync_paid_orders;
    }

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
