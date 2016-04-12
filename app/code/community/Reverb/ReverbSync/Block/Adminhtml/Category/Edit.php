<?php
/**
 * Author: Sean Dunagan
 * Created: 9/11/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $controllerAction = $this->getAction();
        $this->_objectId = $controllerAction->getObjectParamName();
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = $controllerAction->getModuleBlockGroupname();

        $fetch_categories_route = $this->getAction()->getUriPathForAction('updateCategories');
        $fetch_categories_url = $this->getUrl($fetch_categories_route);

        $this->_addButton('fetch_reverb_categories', array(
            'label'     => Mage::helper('ReverbSync')->__('Update Reverb Categories'),
            'onclick'   => 'setLocation(\'' . $fetch_categories_url . '\')'
        ), -1);

        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('back');
    }

    public function getFormActionUrl()
    {
        $uri_path = $this->getAction()->getUriPathForAction('save');
        return $this->getUrl($uri_path);
    }

    public function getHeaderText()
    {
        return Mage::helper('ReverbSync')->__('Sync Reverb Categories');
    }
}
