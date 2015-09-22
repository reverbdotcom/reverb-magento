<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Model_Sync_Shipment_Tracking extends Reverb_ProcessQueue_Model_Task
{
    const JOB_CODE = 'shipment_tracking_sync';

    protected $_default_shipping_provider = 'Other';

    protected $_carrier_code_to_provider_mapping = array(
        'fedex' => 'FedEx',
        'dhlint' => 'DHL',
        'dhl' => 'DHL',
        'ups' => 'UPS',
        'usps' => 'USPS',
        'canada_post' => 'Canada Post',
    );

    public function transmitTrackingDataToReverb(stdClass $argumentsObject)
    {
        // Ensure that we have the necessary data for transmission
        $carrier_code = $argumentsObject->carrier_code;

        $shipping_provider = $this->getReverbShippingProviderByMagentoCarrierCode($carrier_code);
        $argumentsObject->provider = $shipping_provider;
        // Send notification field should always be set to true under current specs 2015/9/22
        $argumentsObject->send_notification = true;

        try
        {
            Mage::helper('ReverbSync/shipment_tracking_sync')->executeShipmentTrackingApiCall($argumentsObject);
        }
        catch(Exception $e)
        {

        }


        return $this->_returnSuccessCallbackResult(null);
    }

    public function executeShipmentTrackingApiCall(stdClass $argumentsObject)
    {

    }

    public function getReverbShippingProviderByMagentoCarrierCode($carrier_code)
    {
        if (isset($this->_carrier_code_to_provider_mapping[$carrier_code]))
        {
            return $this->_carrier_code_to_provider_mapping[$carrier_code];
        }

        return $this->_default_shipping_provider;
    }
}
