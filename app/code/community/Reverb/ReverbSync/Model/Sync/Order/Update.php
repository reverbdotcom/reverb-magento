<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Model_Sync_Order_Update extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_ORDER_NOT_CREATED = 'Reverb Order with id %s has not been created in the Magento system yet';

    public function updateReverbOrderInMagento(stdClass $argumentsObject)
    {
        if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
        {
            $error_message = Mage::helper('ReverbSync/orders_sync')->logOrderSyncDisabledMessage();
            Mage::getModel('reverbSync/log')->logOrderSyncError($error_message);
            return $this->_returnAbortCallbackResult($error_message);
        }

        $reverb_order_number = $argumentsObject->order_number;
        // Check to ensure the order has been created
        $magento_entity_id = Mage::getResourceSingleton('reverbSync/order')
                                ->getMagentoOrderEntityIdByReverbOrderNumber($reverb_order_number);

        if (empty($magento_entity_id))
        {
            // Need to wait for the order to be created
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_ORDER_NOT_CREATED, $reverb_order_number);
            // Set this task to be processed again
            return $this->_returnErrorCallbackResult($error_message);
        }

        $reverb_order_status = $argumentsObject->status;
        $updated_rows = Mage::getResourceSingleton('reverbSync/order')
                                ->updateReverbOrderStatusByMagentoEntityId($magento_entity_id, $reverb_order_status);

        return $updated_rows;
    }
}
