<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 10/28/15
 */

/**
 * THE CALLING BLOCK IS EXPECTED TO CATCH ANY EXCEPTIONS THROWN BY THE METHODS IN THIS CLASS
 *
 * Class Reverb_ReverbSync_Model_Observer_Orders_Update
 */
class Reverb_ReverbSync_Model_Observer_Orders_Update
{
    const ERROR_INVALID_ORDER_ENTITY_ID = 'Magento order entity id %s does not correspond to an order in the system';

    /**
     * Execute functionality which should occur regardless of what order status the update specified
     *
     * @param Varien_Event_Observer $observer
     */
    public function executeMagentoOrderUpdate($observer)
    {
        // Update the shipping address if necessary
        $magento_order_entity_id = $observer->getData('order_entity_id');
        $magentoOrder = $this->_initializeMagentoOrder($magento_order_entity_id);
        $orderUpdateArgumentsObject = $observer->getData('reverb_order_update_arguments_object');
        $baseOrderUpdateHelper = Mage::helper('ReverbSync/orders_update_base');
        /* @var Reverb_ReverbSync_Helper_Orders_Update_Base $baseOrderUpdateHelper */
        $baseOrderUpdateHelper->updateOrderShippingAddressIfNecessary($magentoOrder, $orderUpdateArgumentsObject);
    }

    /**
     * THIS METHOD EXPECTS THE CALLING BLOCK TO CATCH ANY AND ALL EXCEPTIONS THROW BY ITS EXECUTION
     *
     * Execute functionality which should be executed in the event that an order status is "cancelled"
     *
     * @param Varien_Event_Observer $observer
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order
     */
    public function executeMagentoOrderCancel($observer)
    {
        $magento_order_entity_id = $observer->getData('order_entity_id');
        $magentoOrder = $this->_initializeMagentoOrder($magento_order_entity_id);
        $reverb_order_status = $observer->getData('reverb_order_status');
        $cancelOrderUpdateHelper = Mage::helper('ReverbSync/orders_update_cancel');
        /* @var Reverb_ReverbSync_Helper_Orders_Update_Cancel $cancelOrderUpdateHelper */
        $cancelOrderUpdateHelper->executeMagentoOrderCancel($magentoOrder, $reverb_order_status);
    }

    /**
     * THIS METHOD EXPECTS THE CALLING BLOCK TO CATCH ANY AND ALL EXCEPTIONS THROW BY ITS EXECUTION
     *
     * Execute functionality which should be executed in the event that an order status is "paid"
     *
     * @param Varien_Event_Observer $observer
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order
     */
    public function executeMagentoOrderPaid($observer)
    {
        $magento_order_entity_id = $observer->getData('order_entity_id');
        $magentoOrder = $this->_initializeMagentoOrder($magento_order_entity_id);
        $reverb_order_status = $observer->getData('reverb_order_status');
        $orderUpdateArgumentsObject = $observer->getData('reverb_order_update_arguments_object');
        $paidOrderUpdateHelper = Mage::helper('ReverbSync/orders_update_paid');
        /* @var Reverb_ReverbSync_Helper_Orders_Update_Paid $paidOrderUpdateHelper */
        $paidOrderUpdateHelper->executeMagentoOrderPaid($magentoOrder, $reverb_order_status, $orderUpdateArgumentsObject);
    }

    /**
     * It is intended that this method throw an Exception which is caught by the catch block in
     *      Reverb_ReverbSync_Model_Sync_Order_Update::_executeStatusUpdate()
     *
     * @param $magento_order_entity_id
     * @return Mage_Sales_Model_Order
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order
     */
    protected function _initializeMagentoOrder($magento_order_entity_id)
    {
        $magentoOrder = Mage::getModel('sales/order')->load($magento_order_entity_id);
        if ((!is_object($magentoOrder)) || (!$magentoOrder->getId()))
        {
            $error_message = Mage::helper('ReverbSync')
                                 ->__(self::ERROR_INVALID_ORDER_ENTITY_ID, $magento_order_entity_id);
            throw new Reverb_ReverbSync_Model_Exception_Data_Order($error_message);
        }

        return $magentoOrder;
    }
}
