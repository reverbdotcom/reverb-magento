<?php
/**
 * Author: Sean Dunagan
 * Created: 10/27/15
 */

/**
 * This class has been deprecated and is no longer in use
 */
class Reverb_ReverbSync_Model_Category_Magento_Reverb_Mapping extends Mage_Core_Model_Abstract
{
    const MAGENTO_CATEGORY_ID_FIELD = 'magento_category_id';
    const REVERB_CATEGORY_ID_FIELD = 'reverb_category_id';

    protected function _construct()
    {
        $this->_init('reverbSync/category_magento_reverb_mapping');
    }
}
