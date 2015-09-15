<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */

class Reverb_ProcessQueue_Block_Adminhtml_Task_Unique_Edit_Form
    extends Reverb_ProcessQueue_Block_Adminhtml_Task_Edit_Form
    implements Reverb_Base_Block_Adminhtml_Widget_Form_Interface
{
    public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $this->_addTextFieldEditableIfNewOnly($fieldset, 'unique_id', 'Unique Id', true);
        parent::populateFormFieldset($fieldset);
    }
}
