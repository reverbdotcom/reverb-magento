<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Model_Observer_Shipment
{
    const ERROR_SEND_TRACKING_INFO_TO_REVERB = 'An error occurred while attempting to send tracking shipment data to Reverb: %s';

    protected $_reverbShipmentHelper = null;

    public function sendTrackingInfoToReverbIfReverbOrder($observer)
    {
        try
        {
            $shipmentTrackingObject = $observer->getTrack();
            $trackingSyncQueueTaskObject = $this->_getReverbShipmentHelper()
                                                    ->queueShipmentTrackingSyncIfReverbOrder($shipmentTrackingObject);

            if (is_object($trackingSyncQueueTaskObject) && $trackingSyncQueueTaskObject->getId())
            {
                // Attempt to execute the task object
                Mage::helper('reverb_process_queue/task_processor_unique')->processQueueTask($trackingSyncQueueTaskObject);
            }
        }
        catch(Exception $e)
        {
            $error_message = $this->_getReverbShipmentHelper()->__(self::ERROR_SEND_TRACKING_INFO_TO_REVERB, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logShipmentTrackingSyncError($error_message);
        }
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
