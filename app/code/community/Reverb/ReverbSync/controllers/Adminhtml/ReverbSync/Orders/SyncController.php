<?php

/**
 *
 * @category    Reverb
 * @package     Reverb_ReverbSync
 * @author      Sean Dunagan
 * @author      Timur Zaynullin <zztimur@gmail.com>
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/IndexController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Orders_SyncController
    extends Reverb_ProcessQueue_Adminhtml_ProcessQueue_IndexController
{
    const EXCEPTION_BULK_ORDERS_SYNC = 'Error executing a Reverb bulk orders sync: %s';
    const EXCEPTION_PROCESSING_DOWNLOADED_TASKS = 'Error processing downloaded Reverb Order Sync tasks: %s';
    const ERROR_DENIED_ORDER_CREATION_STATUS_UPDATE = 'You do not have permissions to update this task\'s status';
    const NOTICE_QUEUED_ORDERS_FOR_SYNC = 'Order sync in progress. Please wait a few minutes and refresh this page...';
    const NOTICE_PROCESSING_DOWNLOADED_TASKS = 'Processing downloaded orders. Please wait a few minutes and refresh this page...';

    public function indexAction()
    {
        $this->_initAction()
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

        $this->_redirectBasedOnRequestParameter();
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

        $this->_redirectBasedOnRequestParameter();
    }

    /**
     * Returns the appropriate redirect path for the request based on the page which the request came from
     *
     * @return string
     */
    protected function _redirectBasedOnRequestParameter()
    {
        $redirect_controller_param = $this->getRequest()->getParam('redirect_controller');
        $redirect_path_controller = (!empty($redirect_controller_param)) ? $redirect_controller_param : '*';
        $redirect_path = sprintf('*/%s/index', $redirect_path_controller);
        $this->_redirect($redirect_path);
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
        return 'task_id';
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
