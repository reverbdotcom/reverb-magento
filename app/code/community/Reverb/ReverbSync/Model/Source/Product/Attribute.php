<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 2/1/16
 */

class Reverb_ReverbSync_Model_Source_Product_Attribute
{
    /**
     * Returns an array which has Magento product attribute codes mapped to the attribute labels
     *
     * @return array
     */
    public function toOptionArray()
    {
        $product_attributes_array = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $options_array = array();
        $options_array[''] = '';
        foreach($product_attributes_array as $productAttribute)
        {
            $attribute_code = $productAttribute->getAttributeCode();
            $attribute_label = $productAttribute->getFrontendLabel();
            $attribute_label = (!empty($attribute_label)) ? $attribute_label : $attribute_code;
            $options_array[$attribute_code] = $attribute_label;
        }

        asort($options_array);

        return $options_array;
    }
}
