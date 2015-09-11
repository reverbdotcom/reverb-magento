<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Helper_Orders_Retrieval_Creation
    extends Reverb_ReverbSync_Helper_Orders_Retrieval
{
    const ORDERS_CREATION_RETRIEVAL_URL_TEMPLATE = '/api/my/orders/selling/all?created_start_date=%s&created_end_date=%s';
    const MINUTES_IN_PAST_FOR_CREATION_QUERY = 1440;

    public function queueOrderActionByReverbOrderDataObject(stdClass $orderDataObject)
    {
        return $this->_getOrderTaskResourceSingleton()->queueOrderCreationByReverbOrderDataObject($orderDataObject);
    }

    protected function _getAPICallUrlPathTemplate()
    {
        return self::ORDERS_CREATION_RETRIEVAL_URL_TEMPLATE;
    }

    protected function _getMinutesInPastForAPICall()
    {
        return self::MINUTES_IN_PAST_FOR_CREATION_QUERY;
    }

    public function getAPICallDescription()
    {
        return 'retrieveOrderCreations';
    }

    public function getOrderSyncAction()
    {
        return 'creation';
    }
}
