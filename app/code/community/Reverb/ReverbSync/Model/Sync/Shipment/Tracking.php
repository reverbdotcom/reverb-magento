<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Model_Sync_Shipment_Tracking extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_INSUFFICIENT_DATA = "Insufficient data is set on a queue task object for Reverb Shipment Tracking Sync:\nReverb_Order Id: %s\nCarrier Code: %s\nTracking Number: %s";
    const ERROR_SEND_TRACKING_DATA = 'An error occurred while sending tracking data to Reverb: %s';

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
        try
        {
            $reverb_order_id = isset($argumentsObject->reverb_order_id) ? $argumentsObject->reverb_order_id : null;
            $tracking_number = isset($argumentsObject->track_number) ? $argumentsObject->track_number : null;
            // As of 2015/09/22 senc_notification should always be true
            $send_notification = true;
            $carrier_code = isset($argumentsObject->carrier_code) ? $argumentsObject->carrier_code : null;
            // Ensure that we have the necessary data for transmission
            if (empty($reverb_order_id) || empty($tracking_number) || empty($carrier_code))
            {
                $error_message = Mage::helper('ReverbSync/shipment_data')
                                    ->__(self::ERROR_INSUFFICIENT_DATA, $reverb_order_id, $carrier_code, $tracking_number);
                throw new Reverb_ReverbSync_Model_Exception_Data_Shipment_Tracking($error_message);
            }
            $shipping_provider = $this->getReverbShippingProviderByMagentoCarrierCode($carrier_code);

            $api_call_response = Mage::helper('ReverbSync/shipment_tracking_sync')
                                    ->sendShipmentTrackingDataToReverb($reverb_order_id, $tracking_number,
                                                                        $shipping_provider, $send_notification);
        }
        catch(Reverb_ReverbSync_Model_Exception_Data_Shipment_Tracking $e)
        {
            // Since we have insufficient data to process an API call, do not try this queue task again
            $error_message = Mage::helper('ReverbSync/shipment_data')->__(self::ERROR_SEND_TRACKING_DATA, $e->getMessage());
            return $this->_returnAbortCallbackResult($error_message);
        }
        catch(Exception $e)
        {
            $error_message = Mage::helper('ReverbSync/shipment_data')->__(self::ERROR_SEND_TRACKING_DATA, $e->getMessage());
            return $this->_returnErrorCallbackResult($error_message);
        }

        $api_response_as_string = implode("\n", $api_call_response);
        return $this->_returnSuccessCallbackResult($api_response_as_string);
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
