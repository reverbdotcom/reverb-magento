<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Helper_Shipment_Tracking_Sync
    extends Reverb_ReverbSync_Helper_Data
    implements Reverb_ReverbSync_Helper_Api_Adapter_Interface
{
    const ERROR_NO_REVERB_ORDER_ID = 'No Reverb Order Id was defined in an attempt to send a shipment tracking sync call to Reverb';

    const SHIPMENT_TRACKING_URL_PATH_TEMPLATE = '/api/my/orders/selling/%s/ship';
    const PARAM_TRACKING_NUMBER = 'tracking_number';
    const PARAM_SEND_NOTIFICATION = 'send_notification';
    const PARAM_PROVIDER = 'provider';

    /**
     * The calling block is expected to provide a try-catch block in which to execute this request
     *
     * @param int $reverb_order_id
     * @param string $tracking_number
     * @param string $shipping_provider
     * @param bool $send_notification
     * @return array - Containing the json_deocded response
     *
     * @throws Reverb_ReverbSync_Model_Exception_Api_Shipment_Tracking -
     */
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

        // If there are any errors with the api call or response, the method below will throw an Exception
        $response_as_array = $this->_processCurlRequestResponse($post_response_as_json, $curlResource, $post_fields_content);
        // If the response did contain errors, the above method would have thrown an Exception
        return $response_as_array;
    }

    public function getApiLogFileSuffix()
    {
        return 'shipment_tracking';
    }

    public function getExceptionObject($error_message)
    {
        $exceptionObject = new Reverb_ReverbSync_Model_Exception_Api_Shipment_Tracking($error_message);
        return $exceptionObject;
    }

    public function logError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logShipmentTrackingSyncError($error_message);
    }
}
