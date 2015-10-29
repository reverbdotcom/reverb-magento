<?php
/**
 * Author: Sean Dunagan
 * Created: 10/26/15
 */

class Reverb_ReverbSync_Model_Mysql4_Category_Reverb_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('reverbSync/category_reverb');
    }

    public function addReverbCategoryIdFilter(array $reverb_category_ids)
    {
        $this->addFieldToFilter('reverb_category_id', $reverb_category_ids);
        return $this;
    }
}
