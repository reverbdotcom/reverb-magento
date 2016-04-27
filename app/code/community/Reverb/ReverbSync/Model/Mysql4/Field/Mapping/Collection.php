<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/18/16
 */

class Reverb_ReverbSync_Model_Mysql4_Field_Mapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('reverbSync/field_mapping');
    }
}
