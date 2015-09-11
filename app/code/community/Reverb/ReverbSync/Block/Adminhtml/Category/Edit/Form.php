<?php
/**
 * Author: Sean Dunagan
 * Created: 9/11/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Category_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $controllerAction = $this->getAction();
        $helper = Mage::helper('ReverbSync');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getActionUrl(), 'method' => 'post'));
        $form->setUseContainer(true);
        $html_id_prefix = 'ReverbSync_';
        $form->setHtmlIdPrefix($html_id_prefix);

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend' => $helper->__('Magento to Reverb Category Mapping'), 'class'=>'fieldset-wide')
        );

        $this->populateFormFieldset($fieldset);

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset)
    {

    }

    public function getActionUrl()
    {
        $uri_path = $this->getAction()->getUriPathForAction('save');
        return $this->getUrl($uri_path);
    }
} 