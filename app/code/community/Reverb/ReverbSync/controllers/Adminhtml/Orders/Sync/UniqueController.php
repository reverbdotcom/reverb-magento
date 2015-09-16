<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/Unique/IndexController.php');
class Reverb_ReverbSync_Adminhtml_Orders_Sync_UniqueController
    extends Reverb_ProcessQueue_Adminhtml_Unique_IndexController
{
    public function indexAction()
    {
        $module_groupname = $this->getModuleGroupname();
        $module_description = $this->getModuleInstanceDescription();

        $this->loadLayout()
            ->_setActiveMenuValue()
            ->_setSetupTitle(Mage::helper($module_groupname)->__($module_description))
            ->_addBreadcrumb()
            ->_addBreadcrumb(Mage::helper($module_groupname)->__($module_description), Mage::helper($module_groupname)->__($module_description))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_orders_unique_index'))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_orders_task_unique_index'))
            ->renderLayout();
    }

    public function canAdminUpdateStatus()
    {
        return Mage::helper('ReverbSync/orders_sync')->canAdminChangeOrderCreationSyncStatus();
    }

    public function getEditBlockClassname()
    {
        return 'ReverbSync/adminhtml_orders_task_unique_edit';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_orders_task_unique_index';
    }

    public function getUriPathForAction($action)
    {
        $uri_path = sprintf('%s/%s/%s', 'reverbSync', $this->getFormActionsController(), $action);
        return $uri_path;
    }

    public function getControllerDescription()
    {
        return "Reverb Order Creation Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'sales/reverb_order_unique_task_sync';
    }

    public function getModuleInstanceDescription()
    {
        return 'Reverb Order Creation Sync Tasks';
    }

    public function getObjectParamName()
    {
        return 'unique_task';
    }

    public function getObjectDescription()
    {
        return 'Order Creation Sync Task';
    }

    public function getFormActionsController()
    {
        return 'adminhtml_orders_sync_unique';
    }

    public function getFullBackControllerActionPath()
    {
        return ('reverbSync/' . $this->getFormBackControllerActionPath());
    }

    public function getFormBackControllerActionPath()
    {
        return 'adminhtml_orders_sync_unique/index';
    }

    protected function _getModuleBlockGroupname()
    {
        return 'ReverbSync';
    }
}
