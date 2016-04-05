<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/5/16
 */

class Reverb_ReverbSync_Helper_Api_Adapter_Category
    extends Reverb_ReverbSync_Helper_Data
    implements Reverb_ReverbSync_Helper_Api_Adapter_Interface
{
    const ERROR_NO_CATEGORIES_ARRAY_IN_RESPONSE = "No 'categories' array was found in the response returned from the Reverb Category Fetch API: %s";

    const CATEGORY_FETCH_API_URI = 'api/categories/flat';

    /**
     * Execute a cURL request to fetch the latest Reverb Category listings
     *
     * The calling block is expected to catch exceptions thrown by this method
     *
     * @return array
     * @throws Reverb_ReverbSync_Model_Exception_Api_Category_Fetch
     */
    public function executeCategoryAPIFetch()
    {
        $fetch_categories_api_url = $this->_getFetchCategoriesEndpointUrl();
        $curlResource = $this->_getCurlResource($fetch_categories_api_url);
        $json_response = $curlResource->read();
        $response_as_array = $this->_processCurlRequestResponse($json_response, $curlResource);

        if ((!isset($response_as_array['categories'])) || !is_array($response_as_array['categories']))
        {
            $json_encode = json_encode($response_as_array, JSON_UNESCAPED_SLASHES);
            $error_message = $this->__(self::ERROR_NO_CATEGORIES_ARRAY_IN_RESPONSE, $json_encode);
            $exceptionToThrow = $this->getExceptionObject($error_message);
            throw $exceptionToThrow;
        }

        return $response_as_array['categories'];
    }

    protected function _getFetchCategoriesEndpointUrl()
    {
        $reverb_api_base_url = $this->_getReverbAPIBaseUrl();
        $url = $reverb_api_base_url . "/" . self::CATEGORY_FETCH_API_URI;
        return $url;
    }

    /**
     * We don't want to log the responses from this request
     *
     * @param $request
     * @param $response
     * @param $api_request
     * @param $status
     */
    protected function _logApiCall($request, $response, $api_request, $status)
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function getApiLogFileSuffix()
    {
        return 'category_fetch';
    }

    /**
     * @inheritdoc
     */
    public function getExceptionObject($error_message)
    {
        $exceptionObject = new Reverb_ReverbSync_Model_Exception_Api_Category_Fetch($error_message);
        return $exceptionObject;
    }

    /**
     * @inheritdoc
     */
    public function logError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logCategoryMappingError($error_message);
    }
}
