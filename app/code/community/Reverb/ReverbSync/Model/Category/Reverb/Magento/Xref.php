<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 */

class Reverb_ReverbSync_Model_Category_Reverb_Magento_Xref extends Mage_Core_Model_Abstract
{
    const REVERB_CATEGORY_UUID_FIELD = 'reverb_category_uuid';
    const MAGENTO_CATEGORY_ID_FIELD = 'magento_category_id';

    protected function _construct()
    {
        $this->_init('reverbSync/category_reverb_magento_xref');
    }
}
