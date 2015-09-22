<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Model_Sync_Shipment_Tracking extends Reverb_ProcessQueue_Model_Task
{
    public function transmitTrackingDataToReverb(stdClass $argumentsObject)
    {
        $tracking_number = isset($argumentsObject->tracking_number) ? $argumentsObject->tracking_number : null;

    }
}
