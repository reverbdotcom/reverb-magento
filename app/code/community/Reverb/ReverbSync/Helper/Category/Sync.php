<?php
/**
 * Author: Sean Dunagan
 * Created: 9/11/15
 */

class Reverb_ReverbSync_Helper_Category_Sync extends Reverb_ReverbSync_Helper_Data
{
    const CATEGORY_LIST_API_PATH = '/api/categories';

    public function getReverbCategories()
    {
        $base_url = $this->_getReverbAPIBaseUrl();
        $api_url = $base_url . self::CATEGORY_LIST_API_PATH;

        $curlResource = $this->_getCurlResource($api_url);
        $json_response = $curlResource->read();
        $status = $curlResource->getRequestHttpCode();
        $curlResource->close();

        $curlResource->logRequest();
        // MUST NEED TO CHECK ALL OF THESE CALLS DUE TO LACK OF CONSISTENCY AMONG THEM in first paramter
        $this->_logApiCall($api_url, $json_response, 'fetchCategories', $status);

        $jsonDecodedResponse = json_decode($json_response);

        if (property_exists($jsonDecodedResponse, 'categories'))
        {
            $categories_array = $jsonDecodedResponse->categories;
            if (empty($categories_array))
            {
                return array();
            }

            return $categories_array;
        }

        return array();
    }
}
