<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Model_Adapter_Curl extends Varien_Http_Adapter_Curl
{
    const REQUEST_LOG_TEMPLATE = "\ncurl -X%s %s %s %s";
    const AUTH_TOKEN_HEADER_TEMPLATE = '-H "%s"';
    const POST_DATA_ARGUMENT_TEMPLATE = '--data %s';
    const POST_ERROR_LOG_TEMPLATE = 'The following error occurred with the post above: %s';
    const CURL_ERROR_TEMPLATE = "Curl error number %s occurred with the following error message: %s";

    const REQUEST_LOG_FILE = 'reverb_curl_requests.log';

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

    public function logRequest()
    {
        $x_auth_token_header_to_log = '';
        $http_header = $this->_getOption(CURLOPT_HTTPHEADER);
        if (is_array($http_header))
        {
            foreach($http_header as $header_value)
            {
                if (strpos($header_value, 'X-Auth-Token') !== FALSE)
                {
                    $x_auth_token_header = $header_value;
                    $x_auth_token_header_to_log = sprintf(self::AUTH_TOKEN_HEADER_TEMPLATE, $x_auth_token_header);
                }
            }
        }

        $url_to_log = $this->_getOption(CURLOPT_URL);
        if ($this->_getOption(CURLOPT_PUT))
        {
            $http_method_log = 'PUT';
            $body = $this->_getOption(CURLOPT_POSTFIELDS);
            $body_to_log = sprintf(self::POST_DATA_ARGUMENT_TEMPLATE, $body);
        }
        else if ($this->_getOption(CURLOPT_POST))
        {
            $http_method_log = 'POST';
            $body = $this->_getOption(CURLOPT_POSTFIELDS);
            $body_to_log = sprintf(self::POST_DATA_ARGUMENT_TEMPLATE, $body);
        }
        else
        {
            $http_method_log = 'GET';
            $body_to_log = '';
        }

        $string_to_log = sprintf(self::REQUEST_LOG_TEMPLATE, $http_method_log, $x_auth_token_header_to_log, $url_to_log, $body_to_log);
        Mage::log($string_to_log, null, self::REQUEST_LOG_FILE);

        $status = $this->getRequestHttpCode();
        $status_as_int = intval($status);
        if ($status_as_int == 0)
        {
            $curl_error = $this->getCurlErrorMessage();
            $error_string_to_log = sprintf(self::POST_ERROR_LOG_TEMPLATE, $curl_error);
            Mage::log($error_string_to_log, null, self::REQUEST_LOG_FILE);
        }
    }

    public function getCurlErrorMessage()
    {
        $curl_error = $this->getCurlError();
        if (!empty($curl_error))
        {
            $curl_error_number = $this->getCurlErrorNumber();
            return sprintf(self::CURL_ERROR_TEMPLATE, $curl_error_number, $curl_error);
        }

        return false;
    }

    public function getCurlError()
    {
        return curl_error($this->_getResource());
    }

    public function getCurlErrorNumber()
    {
        return curl_errno($this->_getResource());
    }

    protected function _getOption($option)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : null;
    }
}
