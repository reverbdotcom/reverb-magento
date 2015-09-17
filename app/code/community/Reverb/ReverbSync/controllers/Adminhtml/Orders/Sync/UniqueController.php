<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/Unique/IndexController.php');
class Reverb_ReverbSync_Adminhtml_Orders_Sync_UniqueController
    extends Reverb_ProcessQueue_Adminhtml_Unique_IndexController
{
    const EXCEPTION_LOAD_UNIQUE_TASK = 'An exception occurred while attempting to load a unique order task to manually act on the task: %s';
    const EXCEPTION_ACT_ON_TASK = 'An error occurred while acting on the task for the order with Reverb Order Id %s: %s';
    const GENERIC_ADMIN_FACING_ERROR_MESSAGE = 'An error occurred with your request. Please try again.';
    const SUCCESS_TASK_ACTION = 'The attempt to %s the Sync of Reverb Order with id %s has completed.';

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

    public function actOnTaskAction()
    {
        try
        {
            $reverb_order_id = $this->getRequest()->getParam('task_id');
            $uniqueQueueTask = Mage::getModel('reverb_process_queue/task_unique')->load($reverb_order_id);
            if ((!is_object($uniqueQueueTask)) || (!$uniqueQueueTask->getId()))
            {
                throw new Exception('An invalid Unique Task Id was passed to the Reverb Orders Sync Unique Controller: ' . $reverb_order_id);
            }
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_LOAD_UNIQUE_TASK, $e->getMessage());
            $this->_logOrderSyncError($error_message);
            Mage::getSingleton('adminhtml/session')->addError($this->__(self::GENERIC_ADMIN_FACING_ERROR_MESSAGE));
            $exception = new Reverb_ReverbSync_Controller_Varien_Exception($error_message);
            $exception->prepareRedirect('*/*/index');
            throw $exception;
        }

        try
        {
            Mage::helper('reverb_process_queue/task_processor_unique')->processQueueTask($uniqueQueueTask);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_ACT_ON_TASK, $reverb_order_id, $e->getMessage());
            $this->_logOrderSyncError($error_message);
            Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));
            $exception = new Reverb_ReverbSync_Controller_Varien_Exception($error_message);
            $exception->prepareRedirect('*/*/index');
            throw $exception;
        }

        $action_text = $uniqueQueueTask->getActionText();
        $success_message = sprintf(self::SUCCESS_TASK_ACTION, $action_text, $reverb_order_id);
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__($success_message));
        $this->_redirect('*/*/index');
    }

    protected function _logOrderSyncError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
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
