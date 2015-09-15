<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

class Reverb_Base_Block_Adminhtml_Widget_Form_Container
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $controllerAction = $this->getAction();
        $this->_objectId = $controllerAction->getObjectParamName();
        $this->_controller = $controllerAction->getFormBlockName();
        $this->_blockGroup = $controllerAction->getModuleGroupname();

        parent::__construct();
    }

    public function getFormActionUrl()
    {
        $uri_path = $this->getAction()->getUriPathForAction('save');
        return $this->getUrl($uri_path);
    }

    public function getBackUrl()
    {
        $back_uri_path = $this->getAction()->getFullBackControllerActionPath();
        return $this->getUrl($back_uri_path);
    }

    public function getHeaderText()
    {
        $controllerAction = $this->getAction();
        $pageTitle = $this->getPageTitleToRender();
        $groupname = $controllerAction->getModuleGroupname();

        if (!empty($pageTitle))
        {
            return Mage::helper($groupname)->__($pageTitle);
        }

        // We expect the $pageTitle to be passed in, but prepare for the case where it's not
        $objectToEdit = $controllerAction->getObjectToEdit();
        $object_description = $this->getAction()->getObjectDescription();

        if (is_object($objectToEdit) && $objectToEdit->getId())
        {
            $header_text = 'Edit ' . $object_description;
            return Mage::helper($groupname)->__($header_text);
        }

        $header_text = 'Add New ' . $object_description;
        return Mage::helper($groupname)->__($header_text);
    }
} 