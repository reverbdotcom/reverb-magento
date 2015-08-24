<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Sync_Order extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_ORDER_ALREADY_CREATED = 'Order with Reverb Order Number %s already exists in the Magento system with entity_id %s';
    const EXCEPTION_CREATING_ORDER = "Exception occurred while trying to create order for reverb order with number %s: %s\nThe serialized arguments object was: %s";

    protected $_orderCreationHelper = null;

    public function createReverbOrderInMagento(stdClass $argumentsObject)
    {
        $reverb_order_number = $argumentsObject->order_number;

        // Ensure that this order was not already created
        $magento_entity_id = Mage::getResourceSingleton('reverbSync/order')
                                        ->getMagentoOrderEntityIdByReverbOrderNumber($reverb_order_number);
        if (!empty($magento_entity_id))
        {
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_ORDER_ALREADY_CREATED, $reverb_order_number,
                                                                $magento_entity_id);
            Mage::getModel('reverbSync/log')->logOrderSyncError($error_message);
            return $this->_returnAbortCallbackResult($error_message);
        }

        try
        {
            $this->_getOrderCreationHelper()->createMagentoOrder($argumentsObject);
        }
        catch(Exception $e)
        {
            $error_message = Mage::helper('ReverbSync')->__(self::EXCEPTION_CREATING_ORDER, $reverb_order_number,
                                                                $e->getMessage(), serialize($argumentsObject));
            Mage::getModel('reverbSync/log')->logOrderSyncError($error_message);
            return $this->_returnErrorCallbackResult($error_message);
        }
    }

    protected function _getOrderCreationHelper()
    {
        if (is_null($this->_orderCreationHelper))
        {
            $this->_orderCreationHelper = Mage::helper('ReverbSync/orders_creation');
        }

        return $this->_orderCreationHelper;
    }
}
