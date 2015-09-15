<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

interface Reverb_Base_Block_Adminhtml_Widget_Form_Interface
{
    /**
     * This method should add whatever fields are necessary to the form
     *
     * @param $fieldset
     * @return mixed
     */
    public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset);
}