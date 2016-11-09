<?php
/**
 * Author: Sean Dunagan (github: dunagan5887)
 * Date: 11/7/16
 */

/**
 * Class Reverb_ReverbSync_Model_Source_Order_Status
 */
class Reverb_ReverbSync_Model_Source_Order_Status
{
    const PAID_ORDER_STATUSES_CONFIG_PATH = 'ReverbSync/orders_sync/paid_order_statuses';

    /**
     * @var null|array
     */
    protected $_paid_order_statuses_array = null;

    /**
     * Returns an array containing the Reverb Order statuses which have been configured as being "paid" in the system
     *
     * @return array
     */
    public function getPaidOrderStatusesArray()
    {
        if (is_null($this->_paid_order_statuses_array))
        {
            $paid_order_statuses_array = Mage::getStoreConfig(self::PAID_ORDER_STATUSES_CONFIG_PATH);
            if (!is_array($paid_order_statuses_array))
            {
                // If no statuses have been configured as paid, return an empty array
                return array();
            }
            $this->_paid_order_statuses_array = array_keys($paid_order_statuses_array);
        }

        return $this->_paid_order_statuses_array;
    }
}
