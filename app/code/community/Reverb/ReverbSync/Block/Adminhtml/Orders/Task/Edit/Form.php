<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Edit_Form
    extends Reverb_ProcessQueue_Block_Adminhtml_Task_Edit_Form
{
    public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        if ($this->getAction()->canAdminUpdateStatus())
        {
            $status_options_array = Mage::getModel('reverb_process_queue/source_task_status')->getOptionArray();
            $this->_addEditableSelectField($fieldset, 'status', 'Status', $status_options_array, true);
        }
        else
        {
            $status_value = $this->_getValueIfObjectIsSet('status');
            $status_label = Mage::getModel('reverb_process_queue/source_task_status')->getLabelByOptionValue($status_value);
            $helper = $this->getAction()->getModuleHelper();

            $fieldset->addField('status', 'note', array(
                'name'  => 'status',
                'label' => $helper->__('Status'),
                'title' => $helper->__('Status'),
                'text'  => $status_label
            ));
        }

        $this->_addTextFieldEditableIfNewOnly($fieldset, 'object', 'Object', true);
        $this->_addTextFieldEditableIfNewOnly($fieldset, 'method', 'Method', true);
        $this->_addTextFieldEditableIfNewOnly($fieldset, 'status_message', 'Status Message', false);
        $this->_addTextFieldEditableIfNewOnly($fieldset, 'serialized_arguments_object', 'Serialized Arguments Object', false);
    }
}
