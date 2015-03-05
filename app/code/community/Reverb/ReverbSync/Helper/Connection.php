<?php

class Reverb_ReverbSync_Helper_Connection extends Mage_Core_Helper_Abstract {

    public function revConnection() {
        try {
            $emailId = Mage::getStoreConfig('ReverbSync/extension/revEmailId');
            $password = Mage::getStoreConfig('ReverbSync/extension/revPassword');
            $url = Mage::getStoreConfig('ReverbSync/extension/revUrl');
            $revLoginUrl = $url . "/api/auth/email";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $revLoginUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "email=$emailId&password=$password");
            $data = json_decode(curl_exec($ch), TRUE);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $access_token = "";
            $access_token = $data["token"];
            if ($access_token) {
                return $access_token;
            } else {
                Mage::log($status);

            }
        } catch (Exception $e) {
            $excp = 'Message: ' . $e -> getMessage();
            Mage::log($excp);
        }

    }

}
