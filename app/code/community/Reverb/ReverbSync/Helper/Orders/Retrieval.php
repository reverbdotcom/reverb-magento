<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Helper_Orders_Retrieval extends Mage_Core_Helper_Abstract
{
    const EXCEPTION_QUEUE_MAGENTO_ORDER_CREATION = "An exception occurred while attempting to queue Magento order creation for Reverb order: %s.\nThe json_encoded order data object was: %s";
    const ORDER_NUMBER_EMPTY = 'An attempt was made to create a Reverb order in Magento without specifying a valid Reverb order number. This order can not be synced.';
    const EXCEPTION_QUEUE_ORDER_CREATION = 'An exception occurred while trying to queue order creation for Reverb order with number %s: %s';
    const ERROR_NO_ORDER_CREATION_QUEUE_ROWS_INSERTED = 'No order creation queue rows were inserted for Reverb order with number %s';

    protected $_moduleName = 'ReverbSync';

    protected $_logModel = null;
    protected $orderTaskResourceSingleton = null;

    public function queueReverbOrderCreations()
    {
        $reverb_orders_json = $this->_retrieveOrdersJsonFromReverb();

        $orders_array = $reverb_orders_json->orders;

        foreach ($orders_array as $orderDataObject)
        {
            try
            {
                $this->_attemptToQueueMagentoOrderCreation($orderDataObject);
            }
            catch(Exception $e)
            {
                $error_message = $this->__(self::EXCEPTION_QUEUE_MAGENTO_ORDER_CREATION, $e->getMessage(), json_encode($orderDataObject));
                $this->_logError($error_message);
                $exceptionToLog = new Exception($error_message);
                Mage::logException($exceptionToLog);
            }
        }

        return true;
    }

    protected function _attemptToQueueMagentoOrderCreation(stdClass $orderDataObject)
    {
        $order_number = $orderDataObject->order_number;
        if (empty($order_number))
        {
            $error_message = $this->__(self::ORDER_NUMBER_EMPTY);
            throw new Exception($error_message);
        }

        try
        {
            $row_was_inserted = $this->_getOrderTaskResourceSingleton()
                                        ->queueOrderCreationByReverbOrderDataObject($orderDataObject);
            
            if (empty($row_was_inserted))
            {
                $error_message = $this->__(self::ERROR_NO_ORDER_CREATION_QUEUE_ROWS_INSERTED, $order_number);
                throw new Exception($error_message);
            }
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_QUEUE_ORDER_CREATION, $order_number, $e->getMessage());
            throw new Exception($error_message);
        }

        return true;
    }

    /**
     * MUST Actually implement this API call
     *
     */
    protected function _retrieveOrdersJsonFromReverb()
    {
        $test_file_path = Mage::getBaseDir('var') . DS . 'test' . DS . 'orders_api_call.txt';
        $raw_response = file_get_contents($test_file_path);

        $json_decoded_response = json_decode($raw_response);

        return $json_decoded_response;
    }

    protected function _getOrderTaskResourceSingleton()
    {
        if (is_null($this->orderTaskResourceSingleton))
        {
            $this->orderTaskResourceSingleton = Mage::getResourceSingleton('reverbSync/task_order');
        }

        return $this->orderTaskResourceSingleton;
    }

    protected function _logError($error_message)
    {
        $this->_getLogModel()->logOrderSyncError($error_message);
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
