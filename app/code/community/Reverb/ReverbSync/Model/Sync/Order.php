<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Sync_Order extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_ORDER_ALREADY_CREATED = 'Order with Reverb Order Number %s already exists in the Magento system with entity_id %s';

    public function createReverbOrderInMagento(stdClass $argumentsObject)
    {
        $reverb_order_number = $argumentsObject->order_number;

        // Ensure that this order was not already created
        $magento_entity_id = Mage::getResourceSingleton('reverbSync/order')
                                        ->getMagentoOrderEntityIdByReverbOrderNumber($reverb_order_number);
        if (!empty($magento_entity_id))
        {
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_ORDER_ALREADY_CREATED, $reverb_order_number, $magento_entity_id);
            Mage::getModel('reverbSync/log')->logOrderSyncError($error_message);
            return $this->_returnAbortCallbackResult($error_message);
        }
    }
}
