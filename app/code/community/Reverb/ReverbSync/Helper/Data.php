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

        $fieldsArray = $listingWrapper->getApiCallContentData();
        $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $url = $revUrl . "/api/listings";
        $content = json_encode($fieldsArray);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $x_auth_token = Mage::getStoreConfig('ReverbSync/extension/api_token');

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Auth-Token: $x_auth_token", "Content-type: application/hal+json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        $json_response = curl_exec($curl);

        $this->_logApiCall($content, $json_response, 'createObject');

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $response = json_decode($json_response, true);

        if (is_null($response))
        {
            $response = array();
            $response['message'] = 'The response could not be decoded as a json.';
        }

        if ($status != 201)
        {
            $listingWrapper->setStatus(self::LISTING_STATUS_ERROR);
            //throw new Exception(curl_error($curl));
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
        $revUrl = $this->_getBaseReverbUrl();
        $escaped_sku = urlencode($magento_sku);
        $params = "state=all&sku=" . $escaped_sku;
        $url = $revUrl . "/api/my/listings?" . $params;
        // The Varien Curl Adapter isn't great, could be refactored via extending a subclass
        $curlResource = $this->_getCurlResource($url);
        $curlResource->connect($url);
        //Execute the API call
        $json_response = $curlResource->read();

        $this->_logApiCall($params, $json_response, 'findReverbListingUrlByMagentoSku');

        $status = $curlResource->getInfo(CURLINFO_HTTP_CODE);
        $curlResource->close();

        $response = json_decode($json_response, true);

        if (is_null($response))
        {
            $response = array();
            $response['message'] = 'The response could not be decoded as a json.';
        }

        if ($status != 200) {
            //throw new Exception(curl_error($curl));
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
        $fieldsArray = $listingWrapper->getApiCallContentData();
        $content = json_encode($fieldsArray);
        $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $revUrlToPut  = $revUrl . $url_to_put;


        $curl = curl_init($revUrlToPut);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $x_auth_token = Mage::getStoreConfig('ReverbSync/extension/api_token');

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Auth-Token: $x_auth_token", "Content-type: application/hal+json"));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        // Execute the API call
        $updateStatus = curl_exec($curl);

        $this->_logApiCall($content, $updateStatus, 'updateObject');

        $response = json_decode($updateStatus, true);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($status != 200)
        {
            $updateStatus = json_decode($updateStatus, true);

            $error_message = $updateStatus['message'];
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

    protected function _getBaseReverbUrl()
    {
        return Mage::getStoreConfig('ReverbSync/extension/revUrl');
    }

    protected function _getCurlResource($url, $options_array = array())
    {
        $curlResource = new Varien_Http_Adapter_Curl();
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
        $file = $api_request . '.log';
        Mage::log($message, null, $file);
    }
}
