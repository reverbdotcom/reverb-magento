<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Helper_Orders_Retrieval_Creation
    extends Reverb_ReverbSync_Helper_Orders_Retrieval
{





    const MINUTES_IN_PAST_FOR_CREATION_QUERY = 1440000;







    const EXCEPTION_CHECK_IF_ORDER_ALREADY_SYNCED = 'Error checking to see if order with reverb id %s had already been created in Magento: %s';

    public function queueOrderActionByReverbOrderDataObject(stdClass $orderDataObject)
    {
        $reverb_order_number = $orderDataObject->order_number;
        try
        {
            // The Reverb Order Number is used as the unique_id for the order creation tasks
            $unique_task_primary_key_id = Mage::getResourceSingleton('reverbSync/task_order')
                                            ->getPrimaryKeyByUniqueId($reverb_order_number);
            if (!empty($unique_task_primary_key_id))
            {
                // Order was already synced, don't attempt to queue an order creation
                // Return true because the calling block will throw an exception if we return an empty() value
                return true;
            }
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_CHECK_IF_ORDER_ALREADY_SYNCED, $reverb_order_number, $e->getMessage());
            throw new Exception($error_message);
        }

        return $this->_getOrderTaskResourceSingleton()->queueOrderCreationByReverbOrderDataObject($orderDataObject);
    }

    protected function _getAPICallUrlPathTemplate()
    {
        return Mage::getStoreConfig("ReverbSync/orders_sync/order_sync_reverb_source_url");
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
