<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Model_Mysql4_Task_Shipment_Tracking extends Reverb_ReverbSync_Model_Mysql4_Task_Unique
{
    const ERROR_ADDING_PRODUCT_DATA = 'An error occurred while adding product data to a Reverb Shipment Tracking Sync queue task object for shipment track with tracking number %s: %s';

    const ORDER_CREATION_OBJECT = 'reverbSync/sync_shipment_tracking';
    const ORDER_CREATION_METHOD = 'transmitTrackingDataToReverb';

    protected $_tracking_data_values_to_serialize = array(
        'carrier_code' => 'carrier_code',
        'title' => 'title',
        'number' => 'number',
        'track_number' => 'track_number',
        'parent_id' => 'parent_id',
        'order_id' => 'order_id',
        'store_id' => 'store_id',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'entity_id' => 'entity_id',
    );

    protected $_reverbShipmentHelper = null;

    /**
     * The unique key for the shipment tracking object syncs will be a concatentation of the following:
     *
     *  reverb_order_id
     *  shipping carrier code
     *  tracking_number
     *
     * @param Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject
     * @return int
     */
    public function queueOrderCreationByReverbOrderDataObject(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        $unique_id_key = $this->_getReverbShipmentHelper()->getTrackingSyncQueueTaskUniqueId($shipmentTrackingObject);

        $insert_data_array = $this->_getUniqueInsertDataArrayTemplate(self::ORDER_CREATION_OBJECT,
                                                                        self::ORDER_CREATION_METHOD, $unique_id_key);

        $tracking_data = $shipmentTrackingObject->getData();
        $tracking_data_to_serialize = array_intersect_key($tracking_data, $this->_tracking_data_values_to_serialize);

        try
        {
            $magentoShipment = $shipmentTrackingObject->getShipment();
            if (is_object($magentoShipment))
            {
                $magentoOrder = $magentoShipment->getOrder();
                if (is_object($magentoOrder))
                {
                    $magentoProduct = Mage::helper('ReverbSync/orders_sync')->getReverbOrderItemByOrder($magentoOrder);
                    $tracking_data_to_serialize['sku'] = $magentoProduct->getSku();
                    $tracking_data_to_serialize['name'] = $magentoProduct->getName();
                }
            }
        }
        catch(Exception $e)
        {
            // Do not stop execution, but log the exception
            $error_message = $this->_getReverbShipmentHelper()->__(self::ERROR_ADDING_PRODUCT_DATA, $shipmentTrackingObject->getTrackNumber(), $e->getMessage());
            $this->_getReverbShipmentHelper()->logError($error_message);
        }

        $serialized_arguments_object = serialize($tracking_data_to_serialize);
        $insert_data_array['serialized_arguments_object'] = $serialized_arguments_object;

        $number_of_inserted_rows = $this->_getWriteAdapter()->insert($this->getMainTable(), $insert_data_array);

        return $number_of_inserted_rows;
    }

    public function getQueueTaskIdForShipmentTrackingObject(Mage_Sales_Model_Order_Shipment_Track $shipmentTrackingObject)
    {
        $unique_id_key = $this->_getReverbShipmentHelper()->getTrackingSyncQueueTaskUniqueId($shipmentTrackingObject);
        $task_primary_key = $this->getPrimaryKeyByUniqueId($unique_id_key);

        return $task_primary_key;
    }

    public function getTaskCode()
    {
        return Reverb_ReverbSync_Model_Sync_Shipment_Tracking::JOB_CODE;
    }

    protected function _getReverbShipmentHelper()
    {
        if (is_null($this->_reverbShipmentHelper))
        {
            $this->_reverbShipmentHelper = Mage::helper('ReverbSync/shipment_data');
        }

        return $this->_reverbShipmentHelper;
    }
}
