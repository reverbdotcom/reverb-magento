<?php

class Reverb_ReverbSync_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ERROR_LISTING_CREATION_IS_NOT_ENABLED = 'Reverb listing creation has not been enabled.';

    const API_CALL_LOG_TEMPLATE = "\n%s\n%s\n%s\n";

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
            // Log Exception on reports row
            $listingWrapper->setSyncDetails($e->getMessage());
            $listingWrapper->setStatus(self::LISTING_STATUS_ERROR);
        }
        catch(Exception $e)
        {
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
        $rev_url = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $url = $rev_url . "/api/listings";
        // Get post body content for API Request
        $fieldsArray = $listingWrapper->getApiCallContentData();
        $content = json_encode($fieldsArray);
        // Execute API Request via CURL
        $curlResource = $this->_getCurlResource($url);
        $post_response_as_json = $curlResource->executePostRequest($content);
        $status = $curlResource->getRequestHttpCode();
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($content, $post_response_as_json, 'createObject');
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
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($params, $json_response, 'findReverbListingUrlByMagentoSku');

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
        // Close the CURL Resource
        $curlResource->close();
        // Log the response
        $this->_logApiCall($content, $put_response_as_json, 'updateObject');

        $response = json_decode($put_response_as_json, true);

        if ($status != 200)
        {
            $error_message = $response['message'];
            $listingWrapper->setSyncDetails($error_message);
            $listingWrapper->setStatus(self::LISTING_STATUS_ERROR);

            throw new Exception($error_message);
        }

        $listingWrapper->setSyncDetails(null);
        $listingWrapper->setStatus(self::LISTING_STATUS_SUCCESS);
        $listing_response = isset($response['listing']) ? $response['listing'] : array();
        $web_url = $this->_getWebUrlFromListingResponseArray($listing_response);

        return $web_url;
    }

    protected function _getReverbAPIBaseUrl()
    {
        return Mage::getStoreConfig('ReverbSync/extension/revUrl');
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

    protected function _logApiCall($request, $response, $api_request)
    {
        $message = sprintf(self::API_CALL_LOG_TEMPLATE, $api_request, $request, $response);
        $file = 'reverb_' . $api_request . '.log';
        Mage::log($message, null, $file);
    }
}
