<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Helper_Shipment_Data extends Mage_Core_Helper_Abstract
{
    const EXCEPTION_QUEUE_TRACKING_SYNC = 'An error occurred while attempting to queue shipment tracking sync with Reverb for tracking object with id %s: %s';
    const EXCEPTION_GET_REVERB_ORDER_ID = 'An error occurred while trying to obtain the Reverb Order Id for shipment tracking object with id %s: %s';
    const ERROR_NO_CARRIER_CODE_OR_TRACKING_NUMBER = 'An attempt was made to obtain the queue task unique id for a Reverb Shipment Tracking sync on a tracking object with insufficient data. Reverb Order Id was %s, Carrier Code was %s, and tracking number was %s.';
    const ERROR_CREATE_TRACKING_SYNC_QUEUE_OBJECT = 'System was unable to create a queue task object for shipment tracking object with id %s';

    protected $_moduleName = 'ReverbSync';

    /**
     * @param Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject
     * @return Reverb_ProcessQueue_Model_Task_Unique|null
     */
    public function queueShipmentTrackingSyncIfReverbOrder(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        try
        {
            if (!$this->isTrackingForAReverbOrderShipment($shipmentTrackingObject))
            {
                return null;
            }

            $trackingSyncQueueTaskObject = $this->queueShipmentTrackingSyncWithReverb($shipmentTrackingObject);
            return $trackingSyncQueueTaskObject;
        }
        catch(Exception $e)
        {
            // We shouldn't be catching anything here. If we do, a PHP level exception likely occurred
            $tracking_id = is_object($shipmentTrackingObject) ? $shipmentTrackingObject->getId() : null;
            $error_message = $this->__(self::EXCEPTION_QUEUE_TRACKING_SYNC, $tracking_id, $e->getMessage());
            $this->_logError($error_message);
        }

        return null;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject
     *
     * @return Reverb_ProcessQueue_Model_Task_Unique
     */
    public function queueShipmentTrackingSyncWithReverb(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        // Ensure we haven't already created a queue task for this tracking number
        $task_primary_key = Mage::getResourceSingleton('reverbSync/task_shipment_tracking')
                                ->getQueueTaskIdForShipmentTrackingObject($shipmentTrackingObject);

        if (!empty($task_primary_key))
        {
            // This shipment tracking object already has an associated sync queue task
            return null;
        }

        $number_of_rows_inserted = Mage::getResourceSingleton('reverbSync/task_shipment_tracking')
                                    ->queueOrderCreationByReverbOrderDataObject($shipmentTrackingObject);
        if (!empty($number_of_rows_inserted))
        {
            $unique_id_key = $this->getTrackingSyncQueueTaskUniqueId($shipmentTrackingObject);
            $queueTaskObject = Mage::getModel('reverb_process_queue/task_unique')->load($unique_id_key, 'unique_id');
            if (is_object($queueTaskObject) && $queueTaskObject->getId())
            {
                return $queueTaskObject;
            }
        }

        $error_message = $this->__(self::ERROR_CREATE_TRACKING_SYNC_QUEUE_OBJECT, $shipmentTrackingObject->getId());
        throw new Exception($error_message);
    }

    public function getTrackingSyncQueueTaskUniqueId(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        /* Even if we already ran this method in the Reverb_ReverbSync_Helper_Shipment_Data::isTrackingForAReverbOrderShipment()
             check, the necessary objects should have been cached on the tracking and shipment objects, which will prevent
             redundant database calls being made */
        $reverb_order_id = $this->getReverbOrderIdForMagentoShipmentTrackingObject($shipmentTrackingObject);
        $carrier_code = $shipmentTrackingObject->getCarrierCode();
        $tracking_number = $shipmentTrackingObject->getTrackNumber();

        if (empty($carrier_code) || empty($tracking_number))
        {
            $error_message = $this->__(self::ERROR_NO_CARRIER_CODE_OR_TRACKING_NUMBER,
                                        $reverb_order_id, $carrier_code, $tracking_number);
            throw new Reverb_ReverbSync_Model_Exception_Data_Shipment_Tracking($error_message);
        }

        return $reverb_order_id . '_' . $carrier_code . '_' . $tracking_number;
    }

    public function isTrackingForAReverbOrderShipment(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        $magento_entity_id = $this->getMagentoOrderEntityIdForTrackingObject($shipmentTrackingObject);
        if(empty($magento_entity_id))
        {
            return false;
        }
        $reverb_order_id = $this->getReverbOrderIdForMagentoShipmentTrackingObject($shipmentTrackingObject);
        if(empty($reverb_order_id))
        {
            return false;
        }

        return true;
    }

    public function getReverbOrderIdForMagentoShipmentTrackingObject(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        try
        {
            if (!is_object($shipmentTrackingObject))
            {
                // If tracking object is not persisted to the
                return null;
            }

            $shipmentObject = $shipmentTrackingObject->getShipment();
            if (is_object($shipmentTrackingObject) && $shipmentTrackingObject->getId())
            {
                $magentoOrder = $shipmentObject->getOrder();
                if (is_object($magentoOrder) && $magentoOrder->getId())
                {
                    return $magentoOrder->getReverbOrderId();
                }
            }

            $magento_entity_order_id = $this->getMagentoOrderEntityIdForTrackingObject($shipmentTrackingObject);
            if (!empty($magento_entity_order_id))
            {
                // Use an adapter query for performance considerations, and because we are likely in an aftersave event
                // observer right now
                $reverb_order_id = Mage::getResourceSingleton('reverbSync/order')
                                        ->getReverbOrderIdByMagentoOrderEntityId($magento_entity_order_id);
                return $reverb_order_id;
            }
        }
        catch(Exception $e)
        {
            $tracking_shipment_id = $shipmentTrackingObject->getId();
            $error_message = $this->__(self::EXCEPTION_GET_REVERB_ORDER_ID, $tracking_shipment_id, $e->getMessage());
            $this->_logError($error_message);
        }

        return null;
    }

    public function getMagentoOrderEntityIdForTrackingObject(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        $magento_order_id = $shipmentTrackingObject->getOrderId();
        if(!empty($magento_order_id))
        {
            // This should always be set for a tracking object which has been persisted to the database
            return $magento_order_id;
        }
        // Handle cases where the order_id is not set
        $shipmentObject = $shipmentTrackingObject->getShipment();
        if (is_object($shipmentObject) && $shipmentObject->getId())
        {
            return $shipmentObject->getOrderId();
        }

        return null;
    }

    protected function _logError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logShipmentTrackingSyncError($error_message);
    }
}
