<?php

class Reverb_ReverbSync_Model_Wrapper_Listing extends Varien_Object
{
    protected $_api_call_content_data = null;
    protected $_magentoProduct = null;

    public function getMagentoProduct()
    {
        return $this->_magentoProduct;
    }

    public function setMagentoProduct($magentoProduct)
    {
        $this->_magentoProduct = $magentoProduct;
        return $this;
    }

    public function getApiCallContentData()
    {
        return $this->_api_call_content_data;
    }

    public function setApiCallContentData($api_call_content_data)
    {
        $this->_api_call_content_data = $api_call_content_data;
        return $this;
    }
}
