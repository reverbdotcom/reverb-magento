<?php

class Reverb_ReverbSync_Helper_Data
    extends Mage_Core_Helper_Abstract
    implements Reverb_ReverbSync_Helper_Api_Adapter_Interface
{
    const MODULE_NOT_ENABLED = 'The Reverb Module is not enabled, so products can not be synced with Reverb. Please enable this functionality in System -> Configuration -> Reverb Configuration -> Reverb Extension';
    const ERROR_LISTING_CREATION_IS_NOT_ENABLED = 'Reverb listing creation has not been enabled.';
    const ERROR_EMPTY_RESPONSE = 'The API call returned an empty response. Curl error message: %s';
    const ERROR_RESPONSE_ERROR = "The API call response contained errors: %s\nCurl error message: %s";
    const ERROR_API_STATUS_NOT_OK = "The API call returned an HTTP status that was not 200: %s.\nURL: %s\nContent: %s\nCurl Error Message: %s";

    // In the event that no configuration value was returned for the base url, default to the sandbox URL
    // It's better to make erroneous calls to the sandbox than to production
    const DEFAULT_REVERB_BASE_API_URL = 'https://sandbox.reverb.com';

    const API_CALL_LOG_TEMPLATE = "\n%s\n%s\n%s\n%s\n";

    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_SUCCESS_REGEX = '/^2[0-9]{2}$/';

    const LISTING_STATUS_ERROR = 0;
    const LISTING_STATUS_SUCCESS = 1;

    /**
     * $fieldsArray should eventually be a model
     *
     * @param $listingWrapper
     */
    public function createOrUpdateReverbListing($product, $do_not_allow_creation = false)
    {
        // Create empty wrapper in the event an exception is thrown
        $listingWrapper = Mage::getModel('reverbSync/wrapper_listing');
        $listingWrapper->setMagentoProduct($product);

        try
        {
            $magento_sku = $product->getSku();
            $reverb_listing_url = $this->findReverbListingUrlByMagentoSku($magento_sku);
            if ($reverb_listing_url)
            {
                $listingWrapper = Mage::getModel('reverbSync/Mapper_Product')->getUpdateListingWrapper($product);
                $reverb_web_url = $this->updateObject($listingWrapper, $reverb_listing_url);
            }
            else if(!$do_not_allow_creation)
            {
                $listingWrapper = Mage::getModel('reverbSync/Mapper_Product')->getCreateListingWrapper($product);
                $reverb_web_url = $this->createObject($listingWrapper);
            }
            else
            {
                // On order placement, only listing update should be allowed, not creation
                return false;
            }

            $listingWrapper->setReverbWebUrl($reverb_web_url);
        }
        // Defining specific catch block for Reverb_ReverbSync_Model_Exception_Status_Error for future customization
        catch(Reverb_ReverbSync_Model_Exception_Status_Error $e)
        {
            Mage::getSingleton('reverbSync/log')->setSessionErrorIfAdminIsLoggedIn($e->getMessage());

            // Log Exception on reports row
            $listingWrapper->setSyncDetails($e->getMessage());
            $listingWrapper->setStatus(self::LISTING_STATUS_ERROR);
        }
        catch(Exception $e)
        {
            Mage::getSingleton('reverbSync/log')->setSessionErrorIfAdminIsLoggedIn($e->getMessage());

            // Log Exception on reports row
            $listingWrapper->setSyncDetails($e->getMessage());
            $listingWrapper->setStatus(self::LISTING_STATUS_ERROR);
        }

        Mage::dispatchEvent('reverb_listing_synced', array('reverb_listing' => $listingWrapper));

        return $listingWrapper;
    }

    /**
     * @param $fieldsArray
     * @param $entityType - Being passed in as 'listings'
     * @return mixed
     * @throws Exception
     */
    public function createObject($listingWrapper)
    {
        // Ensure that listing creation is enabled
        $listing_creation_is_enabled = Mage::helper('ReverbSync/sync_product')->isListingCreationEnabled();
        if (!$listing_creation_is_enabled)
        {
            throw new Reverb_ReverbSync_Model_Exception_Status_Error(self::ERROR_LISTING_CREATION_IS_NOT_ENABLED);
        }

        // Construct URL for API Request
        $rev_url = $this->_getReverbAPIBaseUrl();
        $url = $rev_url . "/api/listings";
        // Get post body content for API Request
        $fieldsArray = $listingWrapper->getApiCallContentData();
        $content = json_encode($fieldsArray);
        // Execute API Request via CURL
        $curlResource = $this->_getCurlResource($url);
        $post_response_as_json = $curlResource->executePostRequest($content);
        $status = $curlResource->getRequestHttpCode();
        // Need to grab any potential errors before closing the resource
        $curl_error_message = $curlResource->getCurlErrorMessage();
        $curlResource->logRequest();
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($content, $post_response_as_json, 'createObject', $status);
        // Decode the json response
        $response = json_decode($post_response_as_json, true);

        if (is_null($response))
        {
            $response = array();
            $response['message'] = 'The response could not be decoded as a json.';
        }

        if ($status != 201)
        {
            $listingWrapper->setStatus(self::LISTING_STATUS_ERROR);

            if (isset($response['errors'])) {
                $errors_messaging = $response['message'] . $response['errors'][key($response['errors'])][0];
                $listingWrapper->setSyncDetails($errors_messaging);
                throw new Exception($errors_messaging);
            } else {
                if (!empty($curl_error_message))
                {
                    $listingWrapper->setSyncDetails($curl_error_message);
                    throw new Exception($curl_error_message);
                }
                $error_message = $response['message'];
                $listingWrapper->setSyncDetails($error_message);
                throw new Exception($error_message);
            }
        }

        $listingWrapper->setStatus(self::LISTING_STATUS_SUCCESS);
        $listingWrapper->setSyncDetails(null);
        $listing_response = isset($response['listing']) ? $response['listing'] : array();
        $web_url = $this->_getWebUrlFromListingResponseArray($listing_response);

        return $web_url;
    }

    /**
     * /api/my/listings?sku=#{CGI.escape(sku)}&
     *
     * Returns self listing link if returned, null otherwise
     *
     * @param $magento_sku
     * @return string|null
     * @throws Exception
     */
    public function findReverbListingUrlByMagentoSku($magento_sku)
    {
        $rev_url = $this->_getReverbAPIBaseUrl();
        $escaped_sku = urlencode($magento_sku);
        $params = "state=all&sku=" . $escaped_sku;
        $url = $rev_url . "/api/my/listings?" . $params;
        // Execute API Request via CURL
        $curlResource = $this->_getCurlResource($url);
        $json_response = $curlResource->read();
        $status = $curlResource->getRequestHttpCode();
        // Need to grab any potential errors before closing the resource
        $curl_error_message = $curlResource->getCurlErrorMessage();
        $curlResource->logRequest();
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($params, $json_response, 'findReverbListingUrlByMagentoSku', $status);

        $response = json_decode($json_response, true);

        if (is_null($response))
        {
            $response = array();
            $response['message'] = 'The response could not be decoded as a json.';
        }

        if ($status != 200) {
            if (isset($response['errors'])) {
                throw new Exception($response['message'] . $response['errors'][key($response['errors'])][0]);
            } else {
                if (!empty($curl_error_message))
                {
                    throw new Exception($curl_error_message);
                }
                throw new Exception($response['message']);
            }

        }

        $listings_array = isset($response['listings']) ? ($response['listings']) : array();
        if (empty($listings_array))
        {
            // If no listings were returned, return null
            return null;
        }
        // The listing we are looking for should be the first element in the $listings_array (index 0)
        $listing_array = is_array($listings_array) ? reset($listings_array) : array();
        $self_links_href = $this->_getUpdatePutLinksHrefFromListingResponseArray($listing_array);

        return $self_links_href;
    }

    protected function _getWebUrlFromListingResponseArray(array $listing_response)
    {
        return isset($listing_response['_links']['web']['href'])
            ? $listing_response['_links']['web']['href'] : null;
    }

    protected function _getUpdatePutLinksHrefFromListingResponseArray(array $listing_response)
    {
        return isset($listing_response['_links']['self']['href'])
                ? $listing_response['_links']['self']['href'] : null;
    }

    public function updateObject($listingWrapper, $url_to_put)
    {
        // Construct the URL for the API call
        $rev_url = $this->_getReverbAPIBaseUrl();
        $rev_url_to_put  = $rev_url . $url_to_put;
        // Get the PUT content for the API call
        $fieldsArray = $listingWrapper->getApiCallContentData();
        $content = json_encode($fieldsArray);
        // Exeucte the API PUT Request as CURL
        $curlResource = $this->_getCurlResource($rev_url_to_put);
        $put_response_as_json = $curlResource->executePutRequest($content);
        $status = $curlResource->getRequestHttpCode();
        // Need to grab any potential errors before closing the resource
        $curl_error_message = $curlResource->getCurlErrorMessage();
        $curlResource->logRequest();
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($content, $put_response_as_json, 'updateObject', $status);

        $response = json_decode($put_response_as_json, true);

        if ($status != 200)
        {
            $listingWrapper->setStatus(self::LISTING_STATUS_ERROR);
            if (!empty($curl_error_message))
            {
                $listingWrapper->setSyncDetails($curl_error_message);
                throw new Exception($curl_error_message);
            }

            $error_message = $response['message'];
            $listingWrapper->setSyncDetails($error_message);

            throw new Exception($error_message);
        }

        $listingWrapper->setSyncDetails(null);
        $listingWrapper->setStatus(self::LISTING_STATUS_SUCCESS);
        $listing_response = isset($response['listing']) ? $response['listing'] : array();
        $web_url = $this->_getWebUrlFromListingResponseArray($listing_response);

        return $web_url;
    }

    public function verifyModuleIsEnabled()
    {
        $isEnabled = Mage::getStoreConfig('ReverbSync/extensionOption_group/module_select');
        if (!$isEnabled)
        {
            throw new Reverb_ReverbSync_Model_Exception_Deactivated(self::MODULE_NOT_ENABLED);
        }

        return true;
    }

    protected function _processCurlRequestResponse($response_as_json, Reverb_ReverbSync_Model_Adapter_Curl $curlResource, $content_body = null)
    {
        $status = $curlResource->getRequestHttpCode();
        // Need to grab any potential errors before closing the resource
        $curl_error_message = $curlResource->getCurlErrorMessage();
        $curlResource->logRequest();
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($content_body, $response_as_json, $this->getApiLogFileSuffix(), $status);
        // Decode the json response
        $response_as_array = json_decode($response_as_json, true);
        // Ensure the status code is of the form 2xx
        if (!$this->_isStatusSuccessful($status))
        {
            $api_url = $curlResource->getOption(CURLOPT_URL);
            $error_message = $this->__(self::ERROR_API_STATUS_NOT_OK, $status, $api_url, $content_body, $curl_error_message);
            $this->_logErrorAndThrowException($error_message);
        }
        // Ensure that the response was not empty
        if (empty($response_as_json))
        {
            $error_message = $this->__(self::ERROR_EMPTY_RESPONSE, $curl_error_message);
            $this->_logErrorAndThrowException($error_message);
        }
        // Ensure that the response did not signal any errors occurred
        if (isset($response_as_array['errors']))
        {
            $errors_as_string = json_encode($response_as_array['errors']);
            $error_message = $this->__(self::ERROR_RESPONSE_ERROR, $errors_as_string, $curl_error_message);
            $this->_logErrorAndThrowException($error_message);
        }

        return $response_as_array;
    }

    protected function _isStatusSuccessful($status)
    {
        return preg_match(self::HTTP_STATUS_SUCCESS_REGEX, $status);
    }

    protected function _logErrorAndThrowException($error_message)
    {
        $this->logError($error_message);
        $exceptionToThrow = $this->getExceptionObject($error_message);
        throw $exceptionToThrow;
    }

    /**
     * This method can be overwritten to return api-call specific exception objects
     *
     * @param string $error_message - Exception message
     * @return Reverb_ReverbSync_Model_Exception_Api
     */
    public function getExceptionObject($error_message)
    {
        $exceptionObject = new Reverb_ReverbSync_Model_Exception_Api($error_message);
        return $exceptionObject;
    }

    /**
     * This method expected to be overridden by subclasses to target api-call specific log files
     * @param $error_message
     */
    public function logError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logSyncError($error_message);
    }

    /**
     * This method expected to be overridden by subclasses to target api-call specific log files
     * @return string
     */
    public function getApiLogFileSuffix()
    {
        return 'curl_request';
    }

    protected function _getReverbAPIBaseUrl()
    {
        $base_url = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        if (empty($base_url))
        {
            $base_url = self::DEFAULT_REVERB_BASE_API_URL;
        }

        return $base_url;
    }

    public function getReverbBaseUrl()
    {
        return $this->_getReverbAPIBaseUrl();
    }

    /**
     * @param $url
     * @param array $options_array
     * @return Reverb_ReverbSync_Model_Adapter_Curl
     */
    protected function _getCurlResource($url, $options_array = array())
    {
        $curlResource = Mage::getModel('reverbSync/adapter_curl');
        $options_array[CURLOPT_SSL_VERIFYHOST] = 0;
        $options_array[CURLOPT_SSL_VERIFYPEER] = 0;
        $options_array[CURLOPT_HEADER] = 0;
        $options_array[CURLOPT_RETURNTRANSFER] = 1;

        $x_auth_token = Mage::getStoreConfig('ReverbSync/extension/api_token');
        $options_array[CURLOPT_HTTPHEADER] = array("X-Auth-Token: $x_auth_token", "Content-type: application/hal+json");

        $options_array[CURLOPT_URL] = $url;

        $curlResource->setOptions($options_array);

        return $curlResource;
    }

    protected function _logApiCall($request, $response, $api_request, $status)
    {
        $message = sprintf(self::API_CALL_LOG_TEMPLATE, $api_request, $request, $status, $response);
        $file = 'reverb_sync_' . $api_request . '.log';
        Mage::log($message, null, $file);
    }
}
