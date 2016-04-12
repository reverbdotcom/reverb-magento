<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 */

class Reverb_ReverbSync_Model_Mysql4_Category_Reverb_Magento_Xref_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('reverbSync/category_reverb_magento_xref');
    }
}
