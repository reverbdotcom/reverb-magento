<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

abstract class Reverb_ReverbSync_Helper_Orders_Retrieval extends Reverb_ReverbSync_Helper_Data
{
    const EXCEPTION_QUEUE_MAGENTO_ORDER_ACTION = "An exception occurred while attempting to queue Magento order %s for Reverb order: %s.\nThe json_encoded order data object was: %s";
    const ORDER_NUMBER_EMPTY = 'An attempt was made to create a Reverb order in Magento without specifying a valid Reverb order number. This order can not be synced.';
    const EXCEPTION_QUEUE_ORDER_ACTION = 'An exception occurred while trying to queue order creation for Reverb order with number %s: %s';
    const ERROR_NO_ORDER_ACTION_QUEUE_ROWS_INSERTED = 'No order creation queue rows were inserted for Reverb order with number %s';

    protected $_moduleName = 'ReverbSync';

    protected $_logModel = null;
    protected $_orderTaskResourceSingleton = null;
    protected $_orderSyncHelper = null;

    abstract public function getOrderSyncAction();

    abstract public function queueOrderActionByReverbOrderDataObject(stdClass $orderDataObject);

    abstract protected function _getAPICallUrlPathTemplate();

    abstract protected function _getHoursInPastForAPICall();

    abstract public function getAPICallDescription();

    public function queueReverbOrderSyncActions()
    {
        if (!$this->_getOrderSyncHelper()->isOrderSyncEnabled())
        {
            $this->_getOrderSyncHelper()->logOrderSyncDisabledMessage();
            return false;
        }

        $reverbOrdersJsonObject = $this->_retrieveOrdersJsonFromReverb();

        if (!is_object($reverbOrdersJsonObject))
        {
            return false;
        }

        $orders_array = $reverbOrdersJsonObject->orders;

        if (!is_array($orders_array))
        {
            return false;
        }

        foreach ($orders_array as $orderDataObject)
        {
            try
            {
                $this->_attemptToQueueMagentoOrderActions($orderDataObject);
            }
            catch(Exception $e)
            {
                $order_sync_action = $this->getOrderSyncAction();
                $error_message = $this->__(self::EXCEPTION_QUEUE_MAGENTO_ORDER_ACTION, $order_sync_action, $e->getMessage(), json_encode($orderDataObject));
                $this->_logError($error_message);
                $exceptionToLog = new Exception($error_message);
                Mage::logException($exceptionToLog);
            }
        }

        return true;
    }

    protected function _attemptToQueueMagentoOrderActions(stdClass $orderDataObject)
    {
        $order_number = $orderDataObject->order_number;
        if (empty($order_number))
        {
            $error_message = $this->__(self::ORDER_NUMBER_EMPTY);
            throw new Exception($error_message);
        }

        try
        {
            $row_was_inserted = $this->queueOrderActionByReverbOrderDataObject($orderDataObject);

            if (empty($row_was_inserted))
            {
                $error_message = $this->__(self::ERROR_NO_ORDER_ACTION_QUEUE_ROWS_INSERTED, $order_number);
                throw new Exception($error_message);
            }
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_QUEUE_ORDER_ACTION, $order_number, $e->getMessage());
            throw new Exception($error_message);
        }

        return true;
    }

    protected function _retrieveOrdersJsonFromReverb()
    {
        $base_url = $this->_getReverbAPIBaseUrl();

        $api_call_url_path_template = $this->_getAPICallUrlPathTemplate();
        $hours_in_past_for_api_call = $this->_getHoursInPastForAPICall();

        $current_gmt_timestamp = Mage::getSingleton('core/date')->gmtTimestamp();
        $past_timestamp = $current_gmt_timestamp - (60 * 60 * 24 * $hours_in_past_for_api_call);
        $current_gmt_datetime = Mage::getSingleton('core/date')->date('c', $current_gmt_timestamp);
        $one_day_ago_gmt_datetime = Mage::getSingleton('core/date')->date('c', $past_timestamp);

        $api_url_path = sprintf($api_call_url_path_template, $one_day_ago_gmt_datetime, $current_gmt_datetime);
        $api_url_path = str_replace('+', '-', $api_url_path);

        $api_url = $base_url . $api_url_path;

        $curlResource = $this->_getCurlResource($api_url);
        $status = $curlResource->getRequestHttpCode();
        //Execute the API call
        $json_response = $curlResource->read();
        $curlResource->close();
        // Log the Response
        $curlResource->logRequest();
        $this->_logApiCall($api_url_path, $json_response, $this->getAPICallDescription(), $status);

        $json_decoded_response = json_decode($json_response);

        return $json_decoded_response;
    }

    protected function _getOrderTaskResourceSingleton()
    {
        if (is_null($this->_orderTaskResourceSingleton))
        {
            $this->_orderTaskResourceSingleton = Mage::getResourceSingleton('reverbSync/task_order');
        }

        return $this->_orderTaskResourceSingleton;
    }

    protected function _logError($error_message)
    {
        $this->_getLogModel()->logOrderSyncError($error_message);
    }

    protected function _getOrderSyncHelper()
    {
        if (is_null($this->_orderSyncHelper))
        {
            $this->_orderSyncHelper = Mage::helper('ReverbSync/orders_sync');
        }

        return $this->_orderSyncHelper;
    }

    protected function _getLogModel()
    {
        if (is_null($this->_logModel))
        {
            $this->_logModel = Mage::getModel('reverbSync/log');
        }

        return $this->_logModel;
    }
}
