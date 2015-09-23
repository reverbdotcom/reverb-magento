<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Helper_Shipment_Tracking_Sync extends Reverb_ReverbSync_Helper_Data
{
    const ERROR_NO_REVERB_ORDER_ID = 'No Reverb Order Id was defined in an attempt to send a shipment tracking sync call to Reverb';

    const SHIPMENT_TRACKING_URL_PATH_TEMPLATE = '/my/orders/selling/%s/ship';
    const PARAM_TRACKING_NUMBER = 'tracking_number';
    const PARAM_SEND_NOTIFICATION = 'send_notification';
    const PARAM_PROVIDER = 'provider';

    public function sendShipmentTrackingDataToReverb($reverb_order_id, $tracking_number, $shipping_provider, $send_notification)
    {
        $api_endpoint_base_url = $this->_getReverbAPIBaseUrl();
        $api_endpoint_url_path = sprintf(self::SHIPMENT_TRACKING_URL_PATH_TEMPLATE, $reverb_order_id);
        $api_url = $api_endpoint_base_url . $api_endpoint_url_path;

        $api_params = array(self::PARAM_TRACKING_NUMBER => $tracking_number,
                            self::PARAM_SEND_NOTIFICATION => $send_notification,
                            self::PARAM_PROVIDER => $shipping_provider);

        $post_fields_content = json_encode($api_params);

        $curlResource = $this->_getCurlResource($api_url);
        $post_response_as_json = $curlResource->executePostRequest($post_fields_content);
        $status = $curlResource->getRequestHttpCode();
        // Need to grab any potential errors before closing the resource
        $curl_error_message = $curlResource->getCurlErrorMessage();
        $curlResource->logRequest();
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($post_fields_content, $post_response_as_json, 'shipmentTracking', $status);
        // Decode the json response
        $response_as_array = json_decode($post_response_as_json, true);

        if ($status != self::HTTP_STATUS_OK)
        {
            $error_message = $this->__(self::ERROR_API_STATUS_NOT_OK, $status, $api_url, $post_fields_content, $curl_error_message);
            Mage::getSingleton('reverbSync/log')->logShipmentTrackingSyncError($error_message);
            throw new Reverb_ReverbSync_Model_Exception_Api_Shipment_Tracking($error_message);
        }

        if (empty($response_as_array))
        {
            $error_message = $this->__(self::ERROR_EMPTY_RESPONSE, $curl_error_message);
            Mage::getSingleton('reverbSync/log')->logShipmentTrackingSyncError($error_message);
            throw new Reverb_ReverbSync_Model_Exception_Api_Shipment_Tracking($error_message);
        }

        if (isset($response['errors']))
        {
            $errors_as_string = implode("\n", $response['errors']);
            $error_message = $this->__(self::ERROR_RESPONSE_ERROR, $errors_as_string, $curl_error_message);
            throw new Reverb_ReverbSync_Model_Exception_Api_Shipment_Tracking($error_message);
        }

        return $response_as_array;
    }
}
