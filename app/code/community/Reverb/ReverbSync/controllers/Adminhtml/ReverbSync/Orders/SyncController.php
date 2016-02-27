<?php

require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/IndexController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Orders_SyncController
    extends Reverb_ProcessQueue_Adminhtml_ProcessQueue_IndexController
{
    const EXCEPTION_LOAD_TASK = 'An exception occurred while attempting to load an order task to manually act on the task: %s';
    const EXCEPTION_BULK_ORDERS_SYNC = 'Error executing a Reverb bulk orders sync: %s';
    const EXCEPTION_PROCESSING_DOWNLOADED_TASKS = 'Error processing downloaded Reverb Order Sync tasks: %s';
    const ERROR_DENIED_ORDER_CREATION_STATUS_UPDATE = 'You do not have permissions to update this task\'s status';
    const GENERIC_ADMIN_FACING_ERROR_MESSAGE = 'An error occurred with your request. Please try again.';
    const EXCEPTION_ACT_ON_TASK = 'An error occurred while acting on a task for the order with Reverb Id %s: %s';
    const NOTICE_TASK_ACTION = 'The attempt to %s the Sync of the Order with Reverb ID %s has completed.';
    const NOTICE_QUEUED_ORDERS_FOR_SYNC = 'Order sync in progress. Please wait a few minutes and refresh this page...';
    const NOTICE_PROCESSING_DOWNLOADED_TASKS = 'Processing downloaded orders. Please wait a few minutes and refresh this page...';

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
        if (!$this->canAdminUpdateStatus())
        {
            $task_param_name = $this->getObjectParamName();
            $task_id = $this->getRequest()->getParam($task_param_name);
            $error_message = sprintf(self::ERROR_DENIED_ORDER_CREATION_STATUS_UPDATE);

            // TODO:
            $this->_getAdminHelper()->throwRedirectException($error_message,
                                                             'adminhtml/ReverbSync_orders_sync/edit',
                                                             array($task_param_name => $task_id)
                                                             );
        }

        parent::saveAction();
    }

    public function bulkSyncAction()
    {
        try
        {
            Mage::helper('ReverbSync')->verifyModuleIsEnabled();

            Mage::helper('ReverbSync/orders_retrieval_creation')->queueReverbOrderSyncActions();
            Mage::helper('ReverbSync/orders_creation_task_processor')->processQueueTasks('order_creation');

            Mage::helper('ReverbSync/orders_retrieval_update')->queueReverbOrderSyncActions();
            Mage::helper('reverb_process_queue/task_processor')->processQueueTasks('order_update');
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_BULK_ORDERS_SYNC, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        Mage::getSingleton('adminhtml/session')->addNotice($this->__(self::NOTICE_QUEUED_ORDERS_FOR_SYNC));
        $this->_redirect('*/*/index');
    }

    public function syncDownloadedAction()
    {
        try
        {
            Mage::helper('ReverbSync')->verifyModuleIsEnabled();

            Mage::helper('ReverbSync/orders_creation_task_processor')->processQueueTasks('order_creation');

            Mage::helper('reverb_process_queue/task_processor')->processQueueTasks('order_update');
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_PROCESSING_DOWNLOADED_TASKS, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        Mage::getSingleton('adminhtml/session')->addNotice($this->__(self::NOTICE_PROCESSING_DOWNLOADED_TASKS));
        $this->_redirect('*/*/index');
    }

    public function actOnTaskAction()
    {
        try
        {
            $task_id = $this->getRequest()->getParam('task_id');
            $queueTask = Mage::getModel('reverb_process_queue/task')->load($task_id);
            if ((!is_object($queueTask)) || (!$queueTask->getId()))
            {
                throw new Exception('An invalid Task Id was passed to the Reverb Orders Sync Controller: ' . $task_id);
            }

            $argumentsObject = $queueTask->convertSerializedArgumentsIntoObject();
            $reverb_order_id = isset($argumentsObject->order_number) ? $argumentsObject->order_number : '';
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_LOAD_TASK, $e->getMessage());
            $this->_logOrderSyncError($error_message);
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        try
        {
            Mage::helper('reverb_process_queue/task_processor')->processQueueTask($queueTask);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_ACT_ON_TASK, $reverb_order_id, $e->getMessage());

            // TODO now it uses protected function as a shortcut
            $this->_logOrderSyncError($error_message);
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        $action_text = $queueTask->getActionText();
        $notice_message = sprintf(self::NOTICE_TASK_ACTION, $action_text, $reverb_order_id);
        Mage::getSingleton('adminhtml/session')->addNotice($this->__($notice_message));
        $this->_redirect('*/*/index');
    }

    public function canAdminUpdateStatus()
    {
        return Mage::helper('ReverbSync/orders_sync')->canAdminChangeOrderUpdateSyncStatus();
    }

    public function getEditBlockClassname()
    {
        return 'ReverbSync/adminhtml_orders_task_edit';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_orders_task_index';
    }

    public function getControllerDescription()
    {
        return "Reverb Orders Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'reverb/reverb_order_task_sync';
    }

    public function getModuleInstanceDescription()
    {
        return 'Reverb Order Update Sync Tasks';
    }

    public function getObjectParamName()
    {
        return 'task';
    }

    public function getObjectDescription()
    {
        return 'Order Update Sync Task';
    }

    public function getIndexActionsController()
    {
        return 'ReverbSync_orders_sync';
    }

    protected function _getModuleBlockGroupname()
    {
        return 'ReverbSync';
    }
}
