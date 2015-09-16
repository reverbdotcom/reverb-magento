<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

abstract class Reverb_Base_Controller_Adminhtml_Form_Abstract
    extends Reverb_Base_Controller_Adminhtml_Abstract
    implements Reverb_Base_Controller_Adminhtml_Form_Interface
{
    const ERROR_INVALID_OBJECT_ID = 'No object with classname %s and id %s was found in the database.';
    const ERROR_NON_PERMITTED_FIELDS_UPDATE = 'An attempt was made to modify field(s) "%s" on a %s object. No hacking of html is allowed :)';
    const ERROR_REQUIRED_FIELDS_NOT_POSTED = 'No values for required field(s) "%s" were posted for the %s object being saved. Please include the missing data and try again.';
    const EXCEPTION_DURING_SAVE_ACTION = 'Error attempting to %s: %s';
    const SUCCESS_OBJECT_SUCESSFULLY_CREATED = '%s has been successfully created.';
    const SUCCESS_OBJECT_SUCESSFULLY_UPDATED = '%s has been successfully updated.';

    // Documentation for these abstract classes is given in Reverb_Base_Controller_Adminhtml_Form_Interface
    abstract public function validateDataAndCreateObject($objectToSave, $posted_object_data);

    abstract public function validateDataAndUpdateObject($objectToSave, $posted_object_data);

    abstract public function getObjectParamName();

    abstract public function getObjectDescription();

    abstract public function getModuleInstance();

    abstract public function getFormBlockName();

    abstract public function getFormActionsController();

    // This class will set this field. It's accessor is given below as getObjectToEdit()
    protected $_objectToEdit = null;

    public function newAction()
    {
        $this->_redirect('*/*/edit');
    }

    public function editAction()
    {
        $objectToEdit = $this->_initializeObjectFromParam();
        $object_id = $this->getRequest()->getParam($this->getObjectParamName());

        $existing_object_was_returned = (is_object($objectToEdit) && $objectToEdit->getId());
        $user_is_adding_a_new_object = empty($object_id);

        if ($existing_object_was_returned || $user_is_adding_a_new_object)
        {
            // NOTE: It is expected that the block used to render the form container for these actions will descend from
            //    Reverb_Base_Block_Adminhtml_Widget_Form_Container
            $block_to_create_classname = $this->getEditBlockClassname();
            $blockToCreate = $this->getLayout()->createBlock($block_to_create_classname);
            $block_to_create_name_in_layout = $this->getModuleGroupname() . '_' . $this->getModuleInstance() . '_edit';
            $blockToCreate->setNameInLayout($block_to_create_name_in_layout);

            $object_description = $this->getObjectDescription();
            if ($user_is_adding_a_new_object)
            {
                // No id was passed in, user is attempting to create a new object
                $page_title_template = 'Add New %';
                $page_title = sprintf($page_title_template, $object_description);
            }
            else
            {
                // An existing object was found with $object_id
                $this->_objectToEdit = $objectToEdit;
                $page_title_template = 'Edit Existing %s with id %s';
                $page_title = sprintf($page_title_template, $object_description, $object_id);
            }

            $blockToCreate->setPageTitleToRender($page_title);

            $this->_setSetupTitle($this->__($page_title));

            $this->loadLayout()->_addContent($blockToCreate);
            $this->renderLayout();
        }
        else
        {
            // An object's id was passed in, but no existing entity with that id was found
            $object_classname = $this->getObjectClassname();
            $error_message = sprintf(self::ERROR_INVALID_OBJECT_ID, $object_classname, $object_id);
            $this->_getSession()->addError(Mage::helper($this->getModuleGroupname())->__($error_message));
            $this->_redirect('*/*/index');
        }
    }

    public function saveAction()
    {
        $objectToSave = $this->_initializeObjectFromParam();
        $object_id = $this->getRequest()->getParam($this->getObjectParamName());

        if ($object_id && !is_object($objectToSave))
        {
            // Object Id was provided but an object was not returned from _initializeObjectFromParam()
            $error_message = sprintf(self::ERROR_INVALID_OBJECT_ID, $this->getObjectClassname(), $object_id);
            $error_message .= ' ' . $this->getObjectDescription() . ' update will not occur.';
            $this->_getSession()->addError(Mage::helper($this->getModuleGroupname())->__($error_message));
            $this->_redirect($this->getFullBackControllerActionPath());
        }
        else
        {
            // The following line is used in the event that we have to log an exception
            $action_occurring = is_object($objectToSave) ? 'update' : 'create';
            try
            {
                $form_element_array_name = $this->getFormElementArrayName();
                $posted_object_data = $this->getRequest()->getParam($form_element_array_name);

                if (!is_object($objectToSave))
                {
                    // User is trying to create a new object
                    $object_classname = $this->getObjectClassname();
                    $objectToSave = Mage::getModel($object_classname);
                    $objectToSave->setCreatedAt(Mage::getModel('core/date')->date());

                    $this->validateDataAndCreateObject($objectToSave, $posted_object_data);

                    $success_message = sprintf(self::SUCCESS_OBJECT_SUCESSFULLY_CREATED, $this->getObjectDescription());
                }
                else
                {
                    $this->validateDataAndUpdateObject($objectToSave, $posted_object_data);
                    $success_message = sprintf(self::SUCCESS_OBJECT_SUCESSFULLY_UPDATED, $this->getObjectDescription());
                }

                $objectToSave->save();
                $this->_getSession()->addSuccess(
                    Mage::helper($this->getModuleGroupname())->__($success_message)
                );
                $redirect_argument = array($this->getObjectParamName() => $objectToSave->getId());
            }
            catch(Reverb_Base_Model_Adminhtml_Exception $e)
            {
                // If we catch an exception of this type, we assume the error message is already specific to this context
                $redirect_argument = $this->_logExceptionAndReturnRedirectArgument($e, $objectToSave);
            }
            catch(Exception $e)
            {
                // TODO TEST THIS
                // If we catch a general exception, describe the context we are working in
                $action_description = strcmp($action_occurring, 'create')
                    ? sprintf('update %s with id %s ', $this->getObjectDescription(), $object_id)
                    : sprintf('create a new %s object ', $this->getObjectDescription());

                $error_message = sprintf(self::EXCEPTION_DURING_SAVE_ACTION, $action_description, $e->getMessage());

                $exceptionToLog = new Exception($error_message, $e->getCode(), $e);
                $redirect_argument = $this->_logExceptionAndReturnRedirectArgument($exceptionToLog, $objectToSave);
            }

            $this->_redirect('*/*/edit', $redirect_argument);
        }
    }

    public function getEditBlockClassname()
    {
        return $this->getModuleGroupname() . '/' . $this->getFormBlockName() . '_edit';
    }

    protected function _logExceptionAndReturnRedirectArgument(Exception $exceptionToLog, $objectBeingActedUpon)
    {
        Mage::log($exceptionToLog->getMessage());
        Mage::logException($exceptionToLog);
        $this->_getSession()->addError(
            Mage::helper($this->getModuleGroupname())->__($exceptionToLog->getMessage())
        );
        $redirect_argument = (is_object($objectBeingActedUpon))
            ?  array($this->getObjectParamName() => $objectBeingActedUpon->getId())
            : array();

        return $redirect_argument;
    }

    protected function _initializeObjectFromParam()
    {
        $object_id = $this->getRequest()->getParam($this->getObjectParamName());
        if ($object_id)
        {
            $object_classname = $this->getObjectClassname();
            $objectToInitialize = Mage::getModel($object_classname)->load($object_id);
            if (is_object($objectToInitialize) && $objectToInitialize->getId())
            {
                $this->_objectToEdit = $objectToInitialize;
                return $this->_objectToEdit;
            }
        }
        return false;
    }

    public function getObjectClassname()
    {
        $objects_module_instance = $this->getModuleInstance();
        $objects_module = $this->getModuleGroupname();
        $object_classname = $objects_module . '/' . $objects_module_instance;

        return $object_classname;
    }

    public function getUriPathForAction($action)
    {
        $uri_path = sprintf('%s/%s/%s', $this->getModuleGroupname(), $this->getFormActionsController(), $action);
        return $uri_path;
    }

    public function getFormBackControllerActionPath()
    {
        return 'index/index';
    }

    public function getFullBackControllerActionPath()
    {
        $module_router = $this->getModuleGroupname();
        return ($module_router . '/' . $this->getFormBackControllerActionPath());
    }

    public function getFormElementArrayName()
    {
        return ($this->getObjectParamName() . '_data');
    }

    public function getObjectToEdit()
    {
        return $this->_objectToEdit;
    }

    protected function _assertDataIsRestrictedToFields($data_posted, $fields_to_restrict_to)
    {
        $non_permitted_fields = array_diff(array_keys($data_posted), $fields_to_restrict_to);
        if (empty($non_permitted_fields))
        {
            return true;
        }
        // Fields which were not permitted to be edited were modified. Throw an exception
        $non_permitted_fields_string = implode(', ', $non_permitted_fields);
        $error_message = sprintf(self::ERROR_NON_PERMITTED_FIELDS_UPDATE, $non_permitted_fields_string, $this->getObjectDescription());
        throw new Reverb_Base_Model_Adminhtml_Exception($error_message);
    }

    protected function _assertRequiredFieldsAreIncluded($data_posted, $required_fields)
    {
        $required_fields_not_posted = array_diff($required_fields, array_keys($data_posted));
        if (empty($required_fields_not_posted))
        {
            $required_fields_not_posted = array();
            // Check to ensure that none of the required fields are empty
            foreach ($required_fields as $field_to_check)
            {
                // The index in the $data_posted should be set due to our check above, but be extra careful
                $posted_value = isset($data_posted[$field_to_check]) ? $data_posted[$field_to_check] : null;
                if (is_null($posted_value) || !strcmp('', $posted_value))
                {
                    $required_fields_not_posted[$field_to_check] = $field_to_check;
                }
            }
        }
        if (!empty($required_fields_not_posted))
        {
            // Fields which were not permitted to be edited were modified. Throw an exception
            $missing_fields_string = implode(', ', array_keys($required_fields_not_posted));
            $error_message = sprintf(self::ERROR_REQUIRED_FIELDS_NOT_POSTED, $missing_fields_string, $this->getObjectDescription());
            throw new Reverb_Base_Model_Adminhtml_Exception($error_message);
        }
        return true;
    }
}
