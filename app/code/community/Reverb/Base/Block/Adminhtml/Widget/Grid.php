<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

class Reverb_Base_Block_Adminhtml_Widget_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_translationHelper = null;

    public function __construct()
    {
        parent::__construct();
        $controllerAction = $this->getAction();
        $grid_path = str_replace('/', '_', $controllerAction->getControllerActiveMenuPath());
        $grid_id = $controllerAction->getModuleGroupname() . '_' . $grid_path;

        $this->setId($grid_id);
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $object_classname = $this->getAction()->getObjectClassname();

        $collection = Mage::getModel($object_classname)->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getRowUrl($row)
    {
        $object_param_name = $this->getAction()->getObjectParamName();
        return $this->getUrl('*/*/edit', array($object_param_name => $row->getId()));
    }

    public function getGridUrl()
    {
        $uri_path = $this->getAction()->getUriPathForAction('ajaxGrid');
        return $this->getUrl($uri_path);
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getTranslationHelper()
    {
        if (is_null($this->_translationHelper))
        {
            $controllerAction = $this->getAction();
            $this->_translationHelper = $controllerAction->getModuleHelper();
        }

        return $this->_translationHelper;
    }
} 