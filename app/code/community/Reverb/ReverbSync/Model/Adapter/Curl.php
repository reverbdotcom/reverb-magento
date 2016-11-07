<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Model_Adapter_Curl
{
    const REQUEST_LOG_TEMPLATE = "\ncurl -i -k -X%s %s \"%s\" %s";
    const HEADER_TEMPLATE = '-H "%s"';
    const POST_DATA_ARGUMENT_TEMPLATE = "--data '%s'";
    const POST_ERROR_LOG_TEMPLATE = 'The following error occurred with the post above: %s';
    const CURL_ERROR_TEMPLATE = "Curl error number %s occurred with the following error message: %s";
    const USER_AGENT_TEMPLATE = 'Reverb-Magento ReverbMagentoVersion=0.9.5 MagentoVersion=%s MagentoDomain=%s';
    const MAGENTO_VERSION_TEMPLATE = '%s';

    const PUT_CUSTOM_REQUEST_VALUE = 'PUT';

    const REQUEST_LOG_FILE = 'reverb_curl_requests.log';

    /**
     * @var null|Reverb_ReverbSync_Model_Log
     */
    protected $_getLogSingleton = null;

    protected function _applyConfig()
    {
        $this->_addCurrentMagentoVersionUserAgent();
        $this->_applyOptions();

        curl_setopt_array($this->_getResource(), $this->_options);

        if (empty($this->_config)) {
            return $this;
        }

        $verifyPeer = isset($this->_config['verifypeer']) ? $this->_config['verifypeer'] : 0;
        curl_setopt($this->_getResource(), CURLOPT_SSL_VERIFYPEER, $verifyPeer);

        $verifyHost = isset($this->_config['verifyhost']) ? $this->_config['verifyhost'] : 0;
        curl_setopt($this->_getResource(), CURLOPT_SSL_VERIFYHOST, $verifyHost);

        foreach ($this->_config as $param => $curlOption) {
            if (array_key_exists($param, $this->_allowedParams)) {
                curl_setopt($this->_getResource(), $this->_allowedParams[$param], $this->_config[$param]);
            }
        }
        return $this;
    }

    protected function _applyOptions()
    {
        curl_setopt_array($this->_getResource(), $this->_options);
    }

    protected function _addCurrentMagentoVersionUserAgent()
    {
        $magento_version = Mage::getVersion();
        $magento_base_web_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $magento_version_string = sprintf(self::MAGENTO_VERSION_TEMPLATE, $magento_version);

        $magento_domain = Mage::helper('reverb_base')->extractDomainFromUrl($magento_base_web_url);

        $user_agent_string = sprintf(self::USER_AGENT_TEMPLATE, $magento_version_string, $magento_domain);

        $this->addOption(CURLOPT_USERAGENT, $user_agent_string);

        $headers_array = $this->_getOption(CURLOPT_HTTPHEADER);
        if (!is_array($headers_array))
        {
            $headers_array = array();
        }
        $headers_array[] = 'X-Magento-Version: ' . $magento_version_string;
        $headers_array[] = 'X-Magento-Domain: ' . $magento_domain;
        $headers_array[] = 'X-Reverb-App: magento';

        $this->addOption(CURLOPT_HTTPHEADER, $headers_array);
    }

    public function read()
    {
        $this->_applyConfig();
        $response = curl_exec($this->_getResource());

        // Remove 100 and 101 responses headers
        while (Zend_Http_Response::extractCode($response) == 100 || Zend_Http_Response::extractCode($response) == 101) {
            $response = preg_split('/^\r?$/m', $response, 2);
            $response = trim($response[1]);
        }

        if (stripos($response, "Transfer-Encoding: chunked\r\n")) {
            $response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $response);
        }

        return $response;
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
        $this->addOption(CURLOPT_CUSTOMREQUEST, self::PUT_CUSTOM_REQUEST_VALUE);
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
        $http_header = $this->_getOption(CURLOPT_HTTPHEADER);
        $http_header_string_to_log = '';

        if (is_array($http_header))
        {
            $http_headers_to_log_array = array();

            foreach($http_header as $header_value)
            {
                $http_headers_to_log_array[] = sprintf(self::HEADER_TEMPLATE, $header_value);
            }

            $http_header_string_to_log = implode(' ' , $http_headers_to_log_array);
        }

        $url_to_log = $this->_getOption(CURLOPT_URL);
        if (!strcmp($this->_getOption(CURLOPT_CUSTOMREQUEST), self::PUT_CUSTOM_REQUEST_VALUE))
        {
            $http_method_log = self::PUT_CUSTOM_REQUEST_VALUE;
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

        $string_to_log = sprintf(self::REQUEST_LOG_TEMPLATE, $http_method_log, $http_header_string_to_log, $url_to_log, $body_to_log);
        $this->_logApiRequestMessage($string_to_log);

        $status = $this->getRequestHttpCode();
        $status_as_int = intval($status);
        if ($status_as_int == 0)
        {
            $curl_error = $this->getCurlErrorMessage();
            $error_string_to_log = sprintf(self::POST_ERROR_LOG_TEMPLATE, $curl_error);
            $this->_logApiRequestMessage($error_string_to_log);
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

    public function getOption($option)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : null;
    }

    protected function _getOption($option)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : null;
    }

    /**
     * The following are copied from the Community Edition 1.9.2 Varien_Http_Adapter_Curl class to account for the fact
     *  that the Enterprise version does not include all of the same methods
     */
    /**
     * Parameters array
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Curl handle
     *
     * @var resource
     */
    protected $_resource;

    /**
     * Allow parameters
     *
     * @var array
     */
    protected $_allowedParams = array(
        'timeout'       => CURLOPT_TIMEOUT,
        'maxredirects'  => CURLOPT_MAXREDIRS,
        'proxy'         => CURLOPT_PROXY,
        'ssl_cert'      => CURLOPT_SSLCERT,
        'userpwd'       => CURLOPT_USERPWD
    );

    /**
     * Array of CURL options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Set array of additional cURL options
     *
     * @param array $options
     * @return Varien_Http_Adapter_Curl
     */
    public function setOptions(array $options = array())
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Add additional option to cURL
     *
     * @param  int $option      the CURLOPT_* constants
     * @param  mixed $value
     * @return Varien_Http_Adapter_Curl
     */
    public function addOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * Add additional options list to curl
     *
     * @param array $options
     *
     * @return Varien_Http_Adapter_Curl
     */
    public function addOptions(array $options)
    {
        $this->_options = $options + $this->_options;
        return $this;
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param array $config
     * @return Varien_Http_Adapter_Curl
     */
    public function setConfig($config = array())
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * Connect to the remote server
     *
     * @deprecated since 1.4.0.0-rc1
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     * @return Varien_Http_Adapter_Curl
     */
    public function connect($host, $port = 80, $secure = false)
    {
        return $this->_applyConfig();
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param string|Zend_Uri_Http $url
     * @param string        $http_ver
     * @param array         $headers
     * @param string        $body
     * @return string Request as text
     */
    public function write($method, $url, $http_ver = '1.1', $headers = array(), $body = '')
    {
        if ($url instanceof Zend_Uri_Http) {
            $url = $url->getUri();
        }
        $this->_applyConfig();

        $header = isset($this->_config['header']) ? $this->_config['header'] : true;
        $options = array(
            CURLOPT_URL                     => $url,
            CURLOPT_RETURNTRANSFER          => true,
            CURLOPT_HEADER                  => $header
        );
        if ($method == Zend_Http_Client::POST) {
            $options[CURLOPT_POST]          = true;
            $options[CURLOPT_POSTFIELDS]    = $body;
        } elseif ($method == Zend_Http_Client::GET) {
            $options[CURLOPT_HTTPGET]       = true;
        }
        if (is_array($headers)) {
            $options[CURLOPT_HTTPHEADER]    = $headers;
        }

        curl_setopt_array($this->_getResource(), $options);

        return $body;
    }

    /**
     * Close the connection to the server
     *
     * @return Varien_Http_Adapter_Curl
     */
    public function close()
    {
        curl_close($this->_getResource());
        $this->_resource = null;
        return $this;
    }

    /**
     * Returns a cURL handle on success
     *
     * @return resource
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = curl_init();
        }
        return $this->_resource;
    }

    /**
     * Get last error number
     *
     * @return int
     */
    public function getErrno()
    {
        return curl_errno($this->_getResource());
    }

    /**
     * Get string with last error for the current session
     *
     * @return string
     */
    public function getError()
    {
        return curl_error($this->_getResource());
    }

    /**
     * Get information regarding a specific transfer
     *
     * @param int $opt CURLINFO option
     * @return mixed
     */
    public function getInfo($opt = 0)
    {
        if (!$opt) {
            return curl_getinfo($this->_getResource());
        }

        return curl_getinfo($this->_getResource(), $opt);
    }

    /**
     * curl_multi_* requests support
     *
     * @param array $urls
     * @param array $options
     * @return array
     */
    public function multiRequest($urls, $options = array())
    {
        $handles = array();
        $result  = array();

        $multihandle = curl_multi_init();

        foreach ($urls as $key => $url) {
            $handles[$key] = curl_init();
            curl_setopt($handles[$key], CURLOPT_URL,            $url);
            curl_setopt($handles[$key], CURLOPT_HEADER,         0);
            curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, 1);
            if (!empty($options)) {
                curl_setopt_array($handles[$key], $options);
            }
            curl_multi_add_handle($multihandle, $handles[$key]);
        }
        $process = null;
        do {
            curl_multi_exec($multihandle, $process);
            usleep(100);
        } while ($process>0);

        foreach ($handles as $key => $handle) {
            $result[$key] = curl_multi_getcontent($handle);
            curl_multi_remove_handle($multihandle, $handle);
        }
        curl_multi_close($multihandle);
        return $result;
    }

    /**
     * @param string $api_message
     */
    protected function _logApiRequestMessage($api_message)
    {
        $this->_getLogSingleton()->logApiRequestMessage($api_message);
    }

    /**
     * @return Reverb_ReverbSync_Model_Log
     */
    protected function _getLogSingleton()
    {
        if (is_null($this->_getLogSingleton))
        {
            $this->_getLogSingleton = Mage::getSingleton('reverbSync/log');
        }

        return $this->_getLogSingleton;
    }
}
