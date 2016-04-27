<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/18/16
 */

class Reverb_ReverbSync_Block_Adminhtml_Field_Mapping_Edit_Form
    extends Reverb_Base_Block_Adminhtml_Widget_Form
    implements Reverb_Base_Block_Adminhtml_Widget_Form_Interface
{
    public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $productAttributeSourceSingleton = Mage::getSingleton('reverbSync/source_product_attribute');
        /* @var $productAttributeSourceSingleton Reverb_ReverbSync_Model_Source_Product_Attribute */
        $options_array = $productAttributeSourceSingleton->toOptionArray();
        $this->_addEditableSelectField($fieldset, 'magento_attribute_code', 'Magento Attribute Code', $options_array);
        $this->_addEditableTextField($fieldset, 'reverb_api_field', 'Reverb API Field');
    }
}
