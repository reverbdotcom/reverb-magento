<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/18/16
 */

class Reverb_ReverbSync_Model_Mysql4_Field_Mapping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('reverbSync/magento_reverb_field_mapping','mapping_id');
    }
}
