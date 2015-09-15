<?php

require_once('Reverb/ProcessQueue/controllers/Adminhtml/Unique/IndexController.php');
class Reverb_ReverbSync_Adminhtml_Orders_SyncController
    extends Reverb_ProcessQueue_Adminhtml_Unique_IndexController
{
    const EXCEPTION_BULK_ORDERS_SYNC = 'An exception occurred while executing a Reverb bulk orders sync: %s';
    const SUCCESS_QUEUED_ORDERS_FOR_SYNC = 'Orders Sync Processed Successfully';
    const ERROR_DENIED_ORDER_CREATION_STATUS_UPDATE = 'You do not have permissions to update this task\'s status';

    public function indexAction()
    {
        $module_groupname = $this->getModuleGroupname();
        $module_description = $this->getModuleInstanceDescription();

        $this->loadLayout()
            ->_setActiveMenuValue()
            ->_setSetupTitle(Mage::helper($module_groupname)->__($module_description))
            ->_addBreadcrumb()
            ->_addBreadcrumb(Mage::helper($module_groupname)->__($module_description), Mage::helper($module_groupname)->__($module_description))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_orders_index'))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_orders_task_index'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if (!Mage::helper('ReverbSync/orders_sync')->canAdminUpdateOrderCreationSyncStatus())
        {
            $task_param_name = $this->getObjectParamName();
            $task_id = $this->getRequest()->getParam($task_param_name);
            $error_message = sprintf(self::ERROR_DENIED_ORDER_CREATION_STATUS_UPDATE);
            Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));
            $exception = new Reverb_ReverbSync_Controller_Varien_Exception();
            $exception->prepareRedirect('reverbSync/adminhtml_orders_sync/edit', array($task_param_name => $task_id));
            throw $exception;
        }

        parent::saveAction();
    }

    public function bulkSyncAction()
    {
        try
        {
            Mage::helper('ReverbSync/orders_retrieval_creation')->queueReverbOrderSyncActions();
            Mage::helper('ReverbSync/orders_creation_task_processor')->processQueueTasks('order_creation');

            Mage::helper('ReverbSync/orders_retrieval_update')->queueReverbOrderSyncActions();
            Mage::helper('reverb_process_queue/task_processor')->processQueueTasks('order_update');
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_BULK_ORDERS_SYNC, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
            Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));

            $redirectException = new Reverb_ReverbSync_Model_Exception_Redirect($error_message);
            $redirectException->prepareRedirect('reverbSync/adminhtml_orders_sync/index');
            throw $redirectException;
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__(self::SUCCESS_QUEUED_ORDERS_FOR_SYNC));
        $this->_redirect('reverbSync/adminhtml_orders_sync/index');
    }

    public function getEditBlockClassname()
    {
        return 'ReverbSync/adminhtml_orders_task_edit';
    }

    public function getUriPathForAction($action)
    {
        $uri_path = sprintf('%s/%s/%s', 'reverbSync', $this->getFormActionsController(), $action);
        return $uri_path;
    }

    public function getControllerDescription()
    {
        return "Reverb Orders Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'sales/reverb_order_sync';
    }

    public function getModuleInstanceDescription()
    {
        return 'Reverb Order Sync Tasks';
    }

    public function getObjectParamName()
    {
        return 'unique_task';
    }

    public function getObjectDescription()
    {
        return 'Order Sync Task';
    }

    public function getFormActionsController()
    {
        return 'adminhtml_orders_sync';
    }

    public function getFullBackControllerActionPath()
    {
        return ('reverbSync/' . $this->getFormBackControllerActionPath());
    }

    public function getFormBackControllerActionPath()
    {
        return 'adminhtml_orders_sync/index';
    }
}
