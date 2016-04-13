<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Helper_Orders_Retrieval_Update extends Reverb_ReverbSync_Helper_Orders_Retrieval
{
    const ORDERS_UPDATE_RETRIEVAL_URL_TEMPLATE = '/api/my/orders/selling/all?updated_start_date=%s';

    // We will fetch 3 days worth of orders at a time. This is to account for cron problems where
    // the user might need to manually sync the orders
    const MINUTES_IN_PAST_FOR_UPDATE_QUERY = 4320;

    protected $_orderUpdateTaskResourceSingleton = null;

    public function queueOrderActionByReverbOrderDataObject(stdClass $orderDataObject)
    {
        return $this->_getOrderUpdateTaskResourceSingleton()->queueOrderUpdateByReverbOrderDataObject($orderDataObject);
    }

    protected function _getAPICallUrlPathTemplate()
    {
        return self::ORDERS_UPDATE_RETRIEVAL_URL_TEMPLATE;
    }

    protected function _getMinutesInPastForAPICall()
    {
        return self::MINUTES_IN_PAST_FOR_UPDATE_QUERY;
    }

    public function getAPICallDescription()
    {
        return 'retrieveOrderUpdates';
    }

    public function getOrderSyncAction()
    {
        return 'update';
    }

    protected function _getOrderUpdateTaskResourceSingleton()
    {
        if (is_null($this->_orderUpdateTaskResourceSingleton))
        {
            $this->_orderUpdateTaskResourceSingleton = Mage::getResourceSingleton('reverbSync/task_order_update');
        }

        return $this->_orderUpdateTaskResourceSingleton;
    }
}
