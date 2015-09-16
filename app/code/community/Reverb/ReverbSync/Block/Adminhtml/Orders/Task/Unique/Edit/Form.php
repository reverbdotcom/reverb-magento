<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Unique_Edit_Form
    extends Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Edit_Form
{
    public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $this->_addTextFieldEditableIfNewOnly($fieldset, 'unique_id', 'Unique Id');
        parent::populateFormFieldset($fieldset);
    }
}
