<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

abstract class Reverb_Base_Block_Adminhtml_Widget_Form
    extends Mage_Adminhtml_Block_Widget_Form
    implements Reverb_Base_Block_Adminhtml_Widget_Form_Interface
{
    const FORM_ELEMENT_FIELD_NAME_TEMPLATE = '%s[%s]';

    abstract public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset);

    protected $_objectToEdit = null;

    protected function _construct()
    {
        parent::_construct();

        $controllerAction = $this->getAction();
        $this->_objectToEdit = $controllerAction->getObjectToEdit();
    }

    protected function _prepareForm()
    {
        $controllerAction = $this->getAction();
        $helper = $controllerAction->getModuleHelper();

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getActionUrl(), 'method' => 'post'));
        $form->setUseContainer(true);
        $html_id_prefix = $controllerAction->getModuleGroupname() . '_';
        $form->setHtmlIdPrefix($html_id_prefix);

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend' => $helper->__($controllerAction->getObjectDescription()), 'class'=>'fieldset-wide')
        );

        $object_id_element_name = $controllerAction->getObjectParamName();

        $object_id = $this->_isObjectBeingEdited() ? $this->_getObjectToEdit()->getId() : '';
        $fieldset->addField($object_id_element_name, 'hidden', array(
            'name' => $object_id_element_name,
            'value' => $object_id
        ));

        $this->populateFormFieldset($fieldset);

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getActionUrl()
    {
        $uri_path = $this->getAction()->getUriPathForAction('save');
        return $this->getUrl($uri_path);
    }

    protected function _addTextFieldEditableIfNewOnly(Varien_Data_Form_Element_Fieldset $fieldset, $field, $name, $required = true)
    {
        if ($this->_isObjectBeingEdited())
        {
            $this->_addNonEditableTextField($fieldset, $field, $name, $required);
        }
        else
        {
            $this->_addEditableTextField($fieldset, $field, $name, $required);
        }
    }

    protected function _addEditableTextField(Varien_Data_Form_Element_Fieldset $fieldset, $field, $name, $required = true)
    {
        $helper = $this->getAction()->getModuleHelper();

        $fieldset->addField($field, 'text', array(
            'name'  => $this->_getFormElementName($field),
            'label' => $helper->__($name),
            'title' => $helper->__($name),
            'value'  => $this->_getValueIfObjectIsSet($field),
            'required' => ((bool)$required)
        ));
    }

    protected function _addNonEditableTextField(Varien_Data_Form_Element_Fieldset $fieldset, $field, $name)
    {
        $helper = $this->getAction()->getModuleHelper();

        $fieldset->addField($field, 'note', array(
            'name'  => $field,
            'label' => $helper->__($name),
            'title' => $helper->__($name),
            'text'  => $this->_getValueIfObjectIsSet($field)
        ));
    }

    /**
     * There is no _addNonEditableSelectField because having an uneditable dropdown rarely makes sense
     * from a UX perspective
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param $field
     * @param $name
     * @param array $options - Must be an array of the form
     *                          array(
    array(
    'label' => 'label_for_this_option',
    'value' =>  'value_for_this_option'
    ),
    array(
    'label' => 'label_for_this_option',
    'value' =>  'value_for_this_option'
    ),
    ...
    ...
    ...
    );
     * @param bool $required - Defaults to true
     */
    protected function _addEditableSelectField
    (Varien_Data_Form_Element_Fieldset $fieldset, $field, $name, array $options, $required = true)
    {
        $helper = $this->getAction()->getModuleHelper();

        $fieldset->addField($field, 'select', array(
            'name'  => $this->_getFormElementName($field),
            'label' => $helper->__($name),
            'title' => $helper->__($name),
            'value'  => $this->_getValueIfObjectIsSet($field),
            'values'   => $options,
            'required' => $required
        ));
    }

    protected function _getFormElementName($field)
    {
        $array_name = $this->getAction()->getFormElementArrayName();
        return sprintf(self::FORM_ELEMENT_FIELD_NAME_TEMPLATE, $array_name, $field);
    }

    protected function _getValueIfObjectIsSet($field)
    {
        return $this->_isObjectBeingEdited()
            ?  $this->_getObjectToEdit()->getData($field)
            : '';
    }

    protected function _isObjectBeingEdited()
    {
        return (is_object($this->_objectToEdit) && $this->_objectToEdit->getId());
    }

    // Allow this value to be cached locally so we don't need to keep grabbing it from the controller
    protected function _getObjectToEdit()
    {
        return $this->_objectToEdit;
    }
}
