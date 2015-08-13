<?php

class Reverb_ReverbSync_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * $fieldsArray should eventually be a model
     *
     * @param $listingWrapper
     */
    public function createOrUpdateReverbListing($listingWrapper)
    {
        try
        {
            $api_call_content_data_array = $listingWrapper->getApiCallContentData();
            $magento_sku = $api_call_content_data_array['sku'];
            $reverb_listing_url = $this->findReverbListingUrlByMagentoSku($magento_sku);
            if ($reverb_listing_url)
            {
                $reverb_web_url = $this->updateObject($listingWrapper, $reverb_listing_url);
            }
            else
            {
                $reverb_web_url = $this->createObject($listingWrapper);
            }

            $listingWrapper->setReverbWebUrl($reverb_web_url);
        }
        catch(Exception $e)
        {
            // Log Exception on reports row
            $listingWrapper->setErrorMessage($e->getMessage());
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
            $listingWrapper->setStatus(0);
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

        $listingWrapper->setStatus(1);
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
        $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $escaped_sku = urlencode($magento_sku);
        $url = $revUrl . "/api/my/listings?state=all&sku=" . $escaped_sku;
        // The Varien Curl Adapter isn't great, could be refactored via extending a subclass
        $curlResource = $this->_getCurlResource($url);
        $curlResource->connect($url);
        $json_response = $curlResource->read();
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
        $updateStatus = curl_exec($curl);
        $response = json_decode($updateStatus, true);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($status != 200)
        {
            $updateStatus = json_decode($updateStatus, true);

            $error_message = $updateStatus['message'];
            $listingWrapper->setSyncDetails($error_message);
            $listingWrapper->setStatus(0);

            throw new Exception($error_message);
        }

        $listingWrapper->setSyncDetails(null);
        $listingWrapper->setStatus(1);
        $listing_response = isset($response['listing']) ? $response['listing'] : array();
        $web_url = $this->_getWebUrlFromListingResponseArray($listing_response);

        return $web_url;
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
}
