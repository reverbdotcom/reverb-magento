<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Sync_Order extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_ORDER_ALREADY_CREATED = "Reverb Order %s already exists in Magento with entity_id %s";
    const EXCEPTION_CREATING_ORDER = "Error creating Reverb Order %s: %s";
    const SUCCESS_ORDER_CREATION = 'The Reverb order has successfully been synced as Magento order with increment id %s';

    protected $_orderCreationHelper = null;

    public function createReverbOrderInMagento(stdClass $argumentsObject)
    {
        if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
        {
            $error_message = Mage::helper('ReverbSync/orders_sync')->getOrderSyncIsDisabledMessage();
            Mage::getModel('reverbSync/log')->logOrderSyncError($error_message);
            return $this->_returnAbortCallbackResult($error_message);
        }

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
            $magentoOrder = $this->_getOrderCreationHelper()->createMagentoOrder($argumentsObject);
        }
        catch(Reverb_ReverbSync_Model_Exception_Deactivated_Order_Sync $e)
        {
            $error_message = $e->getMessage();
            Mage::getModel('reverbSync/log')->logOrderSyncError($error_message);
            return $this->_returnAbortCallbackResult($error_message);
        }
        catch(Exception $e)
        {
            $error_message = Mage::helper('ReverbSync')->__(self::EXCEPTION_CREATING_ORDER, $reverb_order_number, $e->getMessage());
            Mage::getModel('reverbSync/log')->logOrderSyncError($error_message);
            return $this->_returnErrorCallbackResult($error_message);
        }

        $increment_id = $magentoOrder->getIncrementId();
        $success_message = Mage::helper('ReverbSync')->__(self::SUCCESS_ORDER_CREATION, $increment_id);
        return $this->_returnSuccessCallbackResult($success_message);
    }

    /**
     * @return Reverb_ReverbSync_Helper_Orders_Creation
     */
    protected function _getOrderCreationHelper()
    {
        if (is_null($this->_orderCreationHelper))
        {
            $this->_orderCreationHelper = Mage::helper('ReverbSync/orders_creation');
        }

        return $this->_orderCreationHelper;
    }
}
