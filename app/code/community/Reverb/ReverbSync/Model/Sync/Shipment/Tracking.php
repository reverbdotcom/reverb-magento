<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Model_Sync_Shipment_Tracking extends Reverb_ProcessQueue_Model_Task
{
    const JOB_CODE = 'shipment_tracking_sync';

    public function transmitTrackingDataToReverb(stdClass $argumentsObject)
    {
        $tracking_number = isset($argumentsObject->track_number) ? $argumentsObject->track_number : null;

        return $this->_returnSuccessCallbackResult(null);
    }
}
