<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 11/12/15
 */

class Reverb_ReverbSync_Model_Source_Listing_Condition
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    // TODO: These IDs shouldn't be varchars.
    const NONE = '';
    const NON_FUNCTIONING = 'Non Functioning';
    const POOR = 'Poor';
    const FAIR = 'Fair';
    const GOOD = 'Good';
    const VERY_GOOD = 'Very Good';
    const EXCELLENT = 'Excellent';
    const MINT = 'Mint';
    const B_STOCK = 'B-Stock';
    const BRAND_NEW = 'Brand New';

    protected $_valid_conditions_array = array(
        self::NONE, self::NON_FUNCTIONING, self::POOR, self::FAIR, self::GOOD, self::VERY_GOOD, self::EXCELLENT,
        self::MINT, self::B_STOCK, self::BRAND_NEW,
    );

    public function isValidConditionValue($condition_value)
    {
        return in_array($condition_value, $this->_valid_conditions_array);
    }

    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::NONE),
                    'value' => self::NONE
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::NON_FUNCTIONING),
                    'value' => self::NON_FUNCTIONING
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::POOR),
                    'value' => self::POOR
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::FAIR),
                    'value' => self::FAIR
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::GOOD),
                    'value' => self::GOOD
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::VERY_GOOD),
                    'value' => self::VERY_GOOD
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::EXCELLENT),
                    'value' => self::EXCELLENT
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::MINT),
                    'value' => self::MINT
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::B_STOCK),
                    'value' => self::B_STOCK
                ),
                array(
                    'label' => Mage::helper('ReverbSync')->__(self::BRAND_NEW),
                    'value' => self::BRAND_NEW
                )
            );
        }
        return $this->_options;
    }


    /**
     * Get options array for system configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
 
}
