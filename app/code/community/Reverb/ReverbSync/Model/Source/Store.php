<?php
/**
 * Author: Sean Dunagan
 * Created: 10/28/15
 */

class Reverb_ReverbSync_Model_Source_Store
{
    protected $_store_option_hash = null;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $store_option_hash = $this->_getStoreOptionHash();

        $translated_store_to_option_array = array();
        foreach($store_option_hash as $store_id => $store_name)
        {
            $translated_store_to_option_array[] = array(
                'value' => $store_id,
                'label' => Mage::helper('ReverbSync')->__($store_name),
            );
        }

        return $translated_store_to_option_array;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $store_option_hash = $this->_getStoreOptionHash();
        $translated_store_option_hash = array();
        foreach($store_option_hash as $store_id => $store_name)
        {
            $translated_store_option_hash[$store_id] = Mage::helper('ReverbSync')->__($store_name);
        }

        return $translated_store_option_hash;
    }

    public function isAValidStoreId($store_id)
    {
        $store_id = intval($store_id);
        $store_option_hash = $this->_getStoreOptionHash();
        return (isset($store_option_hash[$store_id]));
    }

    protected function _getStoreOptionHash()
    {
        if (is_null($this->_store_option_hash))
        {
            Mage::getSingleton('adminhtml/system_store')->setIsAdminScopeAllowed(true);
            $this->_store_option_hash = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(true);
        }

        return $this->_store_option_hash;
    }
}
