<?php

class Reverb_ReverbSync_Helper_Data extends Mage_Core_Helper_Abstract {

    public function createObject($fieldsArray, $entityType) {
        $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $url = $revUrl . "/api/$entityType";
        $content = json_encode($fieldsArray);
        $curl = curl_init($url);
        $this->_setHttpBasicAuthOnCurlRequest($curl);
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

        } else {

            foreach ($response as $each_member) {
                if (is_array($each_member)) {
                    while (list($key, $value) = each($each_member)) {
                        if ($key == '_links' && is_array($value)) {
                            $revUrl = $value['web']['href'];
                            return $revUrl;
                        }
                    }
                }
            }
        }

    }

  //function to update the product in reverb
  public function updateObject($fieldsArray, $entityType, $revProductId) {

    $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
    $url = $revUrl . "/api/$entityType/$revProductId";
    $content = json_encode($fieldsArray);

    $curl = curl_init($url);
      $this->_setHttpBasicAuthOnCurlRequest($curl);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

      $x_auth_token = Mage::getStoreConfig('ReverbSync/extension/api_token');

    curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Auth-Token: $x_auth_token", "Content-type: application/hal+json"));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    $updateStatus = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($status != 200) {
      $updateStatus = json_decode($updateStatus, true);
      throw new Exception($updateStatus['message']);
    } else {
      return;
    }
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

  function getCurrentDate($format) {
    $dateTime = new DateTime(null, new DateTimeZone('UTC'));
    return $dateTime -> format($format);
  }

    protected function _setHttpBasicAuthOnCurlRequest($curl)
    {
        // HTTP Basic auth credentials hard-coded for the moment, intended to be moved to system.xml config fields
        curl_setopt($curl, CURLOPT_USERPWD, "hacktest:h4ckt3st");
    }

}
