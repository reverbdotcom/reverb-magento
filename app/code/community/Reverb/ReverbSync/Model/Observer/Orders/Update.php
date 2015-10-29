<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 10/28/15
 */

class Reverb_ReverbSync_Model_Observer_Orders_Update
{
    const ERROR_INVALID_ORDER_ENTITY_ID = 'Magento order entity id %s does not correspond to an order in the system';

    public function executeMagentoOrderCancel($magento_order_entity_id, $reverb_order_status)
    {
        $magentoOrder = Mage::getModel('sales/order')->load($magento_order_entity_id);
        if ((!is_object($magentoOrder)) || (!$magentoOrder->getId()))
        {

            $error_message = Mage::helper('ReverbSync')
                                ->__(self::ERROR_INVALID_ORDER_ENTITY_ID, $magento_order_entity_id);
            throw new Reverb_ReverbSync_Model_Exception_Data_Order($error_message);
        }

        Mage::helper('ReverbSync/orders_update_cancel')->executeMagentoOrderCancel($magentoOrder, $reverb_order_status);
    }
}
