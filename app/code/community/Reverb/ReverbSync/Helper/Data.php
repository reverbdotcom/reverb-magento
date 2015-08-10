<?php

class Reverb_ReverbSync_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * $fieldsArray should eventually be a model
     *
     * @param $fieldsArray
     */
    public function createOrUpdateReverbListing($fieldsArray)
    {
        $magento_sku = $fieldsArray['sku'];
        $reverb_listing_url = $this->findReverbListingUrlByMagentoSku($magento_sku);
        if ($reverb_listing_url)
        {
            return $this->updateObject($fieldsArray, $reverb_listing_url);
        }

        return $this->createObject($fieldsArray);
    }

    /**
     * @param $fieldsArray
     * @param $entityType - Being passed in as 'listings'
     * @return mixed
     * @throws Exception
     */
    public function createObject($fieldsArray)
    {
        $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $url = $revUrl . "/api/listings";
        $content = json_encode($fieldsArray);
        $curl = curl_init($url);
        $this->_setHttpBasicAuthOnCurlRequestIfSandbox($curl);
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

        if ($status != 201) {
            //throw new Exception(curl_error($curl));
            if (isset($response['errors'])) {
                throw new Exception($response['message'] . $response['errors'][key($response['errors'])][0]);
            } else {
                throw new Exception($response['message']);
            }

        }

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

    public function updateObject($fieldsArray, $url_to_put)
    {
        $content = json_encode($fieldsArray);
        $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $revUrlToPut  = $revUrl . $url_to_put;


        $curl = curl_init($revUrlToPut);
        $this->_setHttpBasicAuthOnCurlRequestIfSandbox($curl);
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
        if ($status != 200) {
            $updateStatus = json_decode($updateStatus, true);
            throw new Exception($updateStatus['message']);
        }

        $listing_response = isset($response['listing']) ? $response['listing'] : array();
        $web_url = $this->_getWebUrlFromListingResponseArray($listing_response);

        return $web_url;
    }

  public function reverbReports($revProductId, $revProductName, $revProductSku, $revProductInvent, $revProductUrl, $revProductStatus, $revSyncDetails) {

    try {

      $table = (string)Mage::getConfig() -> getTablePrefix() . 'reverb_reports_reverbreport';
      $writeConn = Mage::getSingleton('core/resource') -> getConnection('core_write');
      $readConn = Mage::getSingleton('core/resource') -> getConnection('core_read');
      $existReport = $readConn -> fetchAll("select entity_id from " . $table . " where product_id = '" . $revProductId . "'");
      if (count($existReport) == 0) {
        $writeConn -> query("insert into " . $table . "(product_id,title,product_sku ,inventory,rev_url,status,sync_details,last_synced) values(?,?,?,?,?,?,?,?)", array($revProductId, $revProductName, $revProductSku, $revProductInvent, $revProductUrl, $revProductStatus, $revSyncDetails, $this -> getCurrentDate('Y-m-d H:i:s')));
      } else {
        if ($revProductUrl != null) {
          $writeConn -> query('update ' . $table . ' set title = "' . $revProductName . '", rev_url = "' . $revProductUrl . '", product_sku = "' . $revProductSku . '",inventory = "' . $revProductInvent . '",status = "' . $revProductStatus . '",sync_details = "' . $revSyncDetails . '",last_synced="' . $this -> getCurrentDate('Y-m-d H:i:s') . '" where product_id =' . $revProductId);
        } else {
          $writeConn -> query('update ' . $table . ' set title = "' . $revProductName . '", product_sku = "' . $revProductSku . '",inventory = "' . $revProductInvent . '",status = "' . $revProductStatus . '",sync_details = "' . $revSyncDetails . '",last_synced="' . $this -> getCurrentDate('Y-m-d H:i:s') . '" where product_id =' . $revProductId);
        }
      }
    } catch (Exception $e) {
      $excp = 'Message: ' . $e -> getMessage();
      Mage::log($excp);
    }

    return;
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
        $options_array = $this->_setHttpBasicAuthOnOptionsArrayIfSandbox($options_array);

        $options_array[CURLOPT_URL] = $url;

        $curlResource->setOptions($options_array);

        return $curlResource;
    }

  function getCurrentDate($format) {
    $dateTime = new DateTime(null, new DateTimeZone('UTC'));
    return $dateTime -> format($format);
  }

    protected function _setHttpBasicAuthOnCurlRequestIfSandbox($curl)
    {
        // HTTP Basic auth credentials hard-coded for the moment, intended to be moved to system.xml config fields
        $sandbox_x_auth_token = Mage::getStoreConfig('ReverbSync/extension/sandbox_http_basic_auth_token');
        if (!empty($sandbox_x_auth_token))
        {
            curl_setopt($curl, CURLOPT_USERPWD, $sandbox_x_auth_token);
        }
    }

    protected function _setHttpBasicAuthOnOptionsArrayIfSandbox($options_array)
    {
        // HTTP Basic auth credentials hard-coded for the moment, intended to be moved to system.xml config fields
        $sandbox_x_auth_token = Mage::getStoreConfig('ReverbSync/extension/sandbox_http_basic_auth_token');
        if (!empty($sandbox_x_auth_token))
        {
            $options_array[CURLOPT_USERPWD] = $sandbox_x_auth_token;
        }

        return $options_array;
    }

}
