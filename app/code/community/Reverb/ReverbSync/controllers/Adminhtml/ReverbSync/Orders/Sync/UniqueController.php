<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/Unique/IndexController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Orders_Sync_UniqueController
    extends Reverb_ProcessQueue_Adminhtml_ProcessQueue_Unique_IndexController
{
    const EXCEPTION_LOAD_UNIQUE_TASK = 'An exception occurred while attempting to load a unique order task to manually act on the task: %s';
    const EXCEPTION_ACT_ON_TASK = 'An error occurred while acting on the task for the order with Reverb Order Id %s: %s';
    const GENERIC_ADMIN_FACING_ERROR_MESSAGE = 'An error occurred with your request. Please try again.';
    const EXCEPTION_SYNC_SHIPMENT_TRACKING = 'An error occurred while attempting to sync shipment tracking data with Reverb: %s';
    const NOTICE_TASK_ACTION = 'The attempt to %s the Sync of Reverb Order with id %s has completed.';
    const NOTICE_SYNC_SHIPMENT_TRACKING = 'The attempt to sync shipment tracking data with Reverb has completed';

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
            $task_id = $this->getRequest()->getParam('task_id');
            $uniqueQueueTask = Mage::getModel('reverb_process_queue/task_unique')->load($task_id);
            if ((!is_object($uniqueQueueTask)) || (!$uniqueQueueTask->getId()))
            {
                throw new Exception('An invalid Unique Task Id was passed to the Reverb Orders Sync Unique Controller: ' . $reverb_order_id);
            }
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_LOAD_UNIQUE_TASK, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        try
        {
            Mage::helper('reverb_process_queue/task_processor_unique')->processQueueTask($uniqueQueueTask);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_ACT_ON_TASK, $task_id, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        $action_text = $uniqueQueueTask->getActionText();
        $reverb_order_id = $uniqueQueueTask->getUniqueId();
        $notice_message = sprintf(self::NOTICE_TASK_ACTION, $action_text, $reverb_order_id);
        Mage::getSingleton('adminhtml/session')->addNotice($this->__($notice_message));
        $this->_redirect('*/*/index');
    }

    public function syncShipmentTrackingAction()
    {
        try
        {
            Mage::helper('reverb_process_queue/task_processor_unique')
                ->processQueueTasks(Reverb_ReverbSync_Model_Sync_Shipment_Tracking::JOB_CODE);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_SYNC_SHIPMENT_TRACKING, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        Mage::getSingleton('adminhtml/session')->addNotice($this->__(self::NOTICE_SYNC_SHIPMENT_TRACKING));
        $this->_redirect('*/*/index');
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

    public function getControllerDescription()
    {
        return "Reverb Order Creation and Shipment Tracking Creation Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'reverb/reverb_order_unique_task_sync';
    }

    public function getModuleInstanceDescription()
    {
        return 'Reverb Order Creation and Shipment Tracking Sync Tasks';
    }

    public function getObjectParamName()
    {
        return 'unique_task';
    }

    public function getObjectDescription()
    {
        return 'Sync Task';
    }

    public function getIndexActionsController()
    {
        return 'ReverbSync_orders_sync_unique';
    }

    protected function _getModuleBlockGroupname()
    {
        return 'ReverbSync';
    }
}
