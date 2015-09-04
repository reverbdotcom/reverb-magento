<?php
/**
 * Author: Sean Dunagan
 * Created: 9/3/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation_Currency
{
    protected $_currencyModel = null;

    public function isValidCurrencyCode($currency_code)
    {
        $allowed_currency_symbols_csv_list =
            Mage::getStoreConfig(Mage_CurrencySymbol_Model_System_Currencysymbol::XML_PATH_ALLOWED_CURRENCIES);
        $allowed_currency_symbols_array = explode(',', $allowed_currency_symbols_csv_list);

        return in_array($currency_code, $allowed_currency_symbols_array);
    }

    public function getDefaultCurrencyCode()
    {
        $default_currency_code = Mage::getStoreConfig('currency/options/base');
        return $default_currency_code;
    }

    protected function _getCurrencyModel()
    {
        if (is_null($this->_currencyModel))
        {
            $this->_currencyModel = Mage::getModel('currencysymbol/system_currencysymbol');
        }

        return $this->_currencyModel;
    }
}
