<?php
/**
 * Author: Timur Zaynullin (https://github.com/zztimur)
 * Created: 02/23/2016
 */

class Reverb_ReverbSync_Model_Source_Listing_Offer
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const VALUE_CONFIG = '0';
    const VALUE_YES = 1;
    const VALUE_NO = 2;

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('ReverbSync')->__('Use Config'),
                    'value' => self::VALUE_CONFIG
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__('Yes'),
                    'value' => self::VALUE_YES
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__('No'),
                    'value' => self::VALUE_NO
                ),
            );
        }
        return $this->_options;
    }


    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }


    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
