<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Model_Adapter_Curl extends Varien_Http_Adapter_Curl
{
    public function read()
    {
        $this->_applyConfig();

        return parent::read();
    }

    public function executePostRequest($body)
    {
        $this->addOption(CURLOPT_POST, true);
        $this->addOption(CURLOPT_POSTFIELDS, $body);

        $this->_applyConfig();

        $curl_response = curl_exec($this->_getResource());

        return $curl_response;
    }

    public function executePutRequest($body)
    {
        $this->addOption(CURLOPT_PUT, true);
        $this->addOption(CURLOPT_POSTFIELDS, $body);

        $this->_applyConfig();

        $curl_response = curl_exec($this->_getResource());

        return $curl_response;
    }

    public function getRequestHttpCode()
    {
        return $this->getInfo(CURLINFO_HTTP_CODE);
    }

    protected function _getOption($option)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : null;
    }
}
