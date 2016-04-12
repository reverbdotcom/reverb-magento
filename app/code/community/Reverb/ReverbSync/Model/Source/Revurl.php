<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Model_Source_Revurl
{
    const PRODUCTION_URL = 'https://reverb.com';
    const PRODUCTION_LABEL = 'Reverb.com (Production)';
    const SANDBOX_URL = 'https://sandbox.reverb.com';
    const SANDBOX_LABEL = 'Reverb Sandbox (Testing)';

    /**
     * Returns the Reverb production API endpoint
     *
     * @return string
     */
    public function getProductionUrl()
    {
        return self::PRODUCTION_URL;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::PRODUCTION_URL, 'label' => Mage::helper('ReverbSync')->__(self::PRODUCTION_LABEL)),
            array('value' => self::SANDBOX_URL, 'label' => Mage::helper('ReverbSync')->__(self::SANDBOX_LABEL)),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::PRODUCTION_URL => Mage::helper('ReverbSync')->__(self::PRODUCTION_LABEL),
            self::SANDBOX_URL => Mage::helper('ReverbSync')->__(self::SANDBOX_LABEL),
        );
    }
}
