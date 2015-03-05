<?php

class Reverb_ReverbSync_Helper_Data extends Mage_Core_Helper_Abstract {

    //function to create the product in reverb
    public function createObject($revConnection, $fieldsArray, $entityType) {

        $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
        $url = $revUrl . "/api/$entityType";
        $content = json_encode($fieldsArray);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Auth-Token: $revConnection", "Content-type: application/hal+json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        $json_response = curl_exec($curl);
        Mage::log($json_response, 1, "createResponse.log");
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $response = json_decode($json_response, true);
        if ($status != 201) {
            //throw new Exception(curl_error($curl));
            throw new Exception($response['message']);
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
    public function updateObject($revConnection, $fieldsArray, $entityType, $revProductId) {

        try {
            $revUrl = Mage::getStoreConfig('ReverbSync/extension/revUrl');
            $url = $revUrl . "/api/$entityType/$revProductId";
            $content = json_encode($fieldsArray);

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Auth-Token: $revConnection", "Content-type: application/hal+json"));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
            curl_exec($curl);
            curl_close($curl);

        } catch (Exception $e) {
            $excp = 'Message: ' . $e -> getMessage();
            Mage::log($excp);
        }

        return;
    }

}
