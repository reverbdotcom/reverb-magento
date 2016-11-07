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
    const NON_PAID_ORDER_STATUS_CONFIG_PATH = 'ReverbSync/orders_sync/non_paid_order_statuses';

    /**
     * @var null|array
     */
    protected $_non_paid_order_statuses_array = null;

    /**
     * Returns an array containing the order statuses which should be considered as not having been paid
     *
     * @return array
     */
    public function getNonPaidOrderStatuses()
    {
        if (is_null($this->_non_paid_order_statuses_array))
        {
            $non_paid_order_statuses = Mage::getStoreConfig(self::NON_PAID_ORDER_STATUS_CONFIG_PATH);
            if (!is_array($non_paid_order_statuses))
            {
                // If no statuses have been configured as non-paid, return an empty array
                return array();
            }
            $non_paid_order_statuses = array_keys($non_paid_order_statuses);
            $this->_non_paid_order_statuses_array = $non_paid_order_statuses;
        }

        return $this->_non_paid_order_statuses_array;
    }
}
