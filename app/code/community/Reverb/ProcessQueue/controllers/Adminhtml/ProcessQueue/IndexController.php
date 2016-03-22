<?php

/**
 *
 * @category    Reverb
 * @package     Reverb_ProcessQueue
 * @author      Sean Dunagan
 * @author      Timur Zaynullin <zztimur@gmail.com>
 */

class Reverb_ProcessQueue_Adminhtml_ProcessQueue_IndexController
    extends Reverb_Base_Controller_Adminhtml_Form_Abstract
    implements Reverb_Base_Controller_Adminhtml_Form_Interface
{
    const ERROR_CLEARING_ALL_TASKS = 'An error occurred while clearing all tasks with job code %s: %s';
    const ERROR_CLEARING_SUCCESSFUL_TASKS = 'An error occurred while clearing all successful tasks with job code %s: %s';
    const SUCCESS_CLEARED_ALL_TASKS_WITH_CODE = 'Successfully cleared all tasks with code %s';
    const SUCCESS_CLEARED_SUCCESSFUL_TASKS_WITH_CODE = 'Successfully cleared all successful tasks with code %s';
    const SUCCESS_CLEARED_ALL_TASKS = 'Successfully cleared all tasks';
    const SUCCESS_CLEARED_SUCCESSFUL_TASKS = 'Successfully cleared all successful tasks';
    const EXCEPTION_INVALID_TASK_ID = '%s. Invalid Task ID=%s.';
    const EXCEPTION_ACT_ON_TASK = '%s. Error while trying to run Task ID=%s: %s.';
    const NOTICE_TASK_ACTION = '%s. Task ID=%s is queued (%s).';

    protected $_adminHelper = null;

    /**
     * Allow Queue Tasks to be created via these forms
     *
     * @param $objectToCreate
     * @param $posted_object_data
     * @return mixed
     */
    public function validateDataAndCreateObject($objectToCreate, $posted_object_data)
    {
        $objectToCreate->setLastExecutedAt(null);
        return $objectToCreate->addData($posted_object_data);
    }

    public function validateDataAndUpdateObject($objectToUpdate, $posted_object_data)
    {
        // Only the status field should have been passed
        $new_status = isset($posted_object_data['status']) ? $posted_object_data['status'] : null;
        if (!is_null($new_status))
        {
            $objectToUpdate->setStatus($new_status);
        }

        return $objectToUpdate;
    }

    public function clearAllTasksAction()
    {
        $task_codes = $this->_getTaskCodesParam();
        try
        {
            $rows_deleted = $this->_getTaskProcessor()->deleteAllTasks($task_codes);
        }
        catch(Exception $e)
        {
            $task_codes_string = implode(', ', $task_codes);
            $error_message = $this->__(self::ERROR_CLEARING_ALL_TASKS, $task_codes_string, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        if (!empty($task_code))
        {
            $success_message = $this->__(self::SUCCESS_CLEARED_ALL_TASKS_WITH_CODE, $task_code);
        }
        else
        {
            $success_message = $this->__(self::SUCCESS_CLEARED_ALL_TASKS);
        }

        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect('*/*/index');
    }

    public function clearSuccessfulTasksAction()
    {
        $task_codes = $this->_getTaskCodesParam();
        try
        {
            $rows_deleted = $this->_getTaskProcessor()->deleteSuccessfulTasks($task_codes);
        }
        catch(Exception $e)
        {
            $task_codes_string = implode(', ', $task_codes);
            $error_message = $this->__(self::ERROR_CLEARING_SUCCESSFUL_TASKS, $task_codes_string, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        if (!empty($task_code))
        {
            $success_message = $this->__(self::SUCCESS_CLEARED_SUCCESSFUL_TASKS_WITH_CODE, $task_code);
        }
        else
        {
            $success_message = $this->__(self::SUCCESS_CLEARED_SUCCESSFUL_TASKS);
        }

        $this->_getAdminHelper()->addAdminSuccessMessage($success_message);
        $this->_redirect('*/*/index');
    }

    /**
     * @return array|null
     */
    protected function _getTaskCodesParam()
    {
        $task_codes_param = $this->getRequest()->getParam('task_codes', null);
        $task_codes = explode(';', $task_codes_param);
        if (!is_array($task_codes) || empty($task_codes))
        {
            return null;
        }

        return $task_codes;
    }

    public function getModuleGroupname()
    {
        return 'reverb_process_queue';
    }

    public function getControllerActiveMenuPath()
    {
        return 'system/reverb_process_queue';
    }

    public function getModuleInstanceDescription()
    {
        return 'Process Queue Tasks';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_task_index';
    }

    public function getObjectParamName()
    {
        return 'task';
    }

    public function getObjectDescription()
    {
        return 'Task';
    }

    public function getModuleInstance()
    {
        return 'task';
    }

    public function getFormBlockName()
    {
        return 'adminhtml_task';
    }

    public function getIndexActionsController()
    {
        return 'ProcessQueue_index';
    }

    protected function _getTaskProcessor()
    {
        return Mage::helper('reverb_process_queue/task_processor');
    }

    /**
     * @return Reverb_ReverbSync_Helper_Admin
     */
    protected function _getAdminHelper()
    {
        if (is_null($this->_adminHelper))
        {
            $this->_adminHelper = Mage::helper('ReverbSync/admin');
        }

        return $this->_adminHelper;
    }

    /**
    * Init layout, menu and breadcrumb
    *
    * @return Reverb_ProcessQueue_Adminhtml_ProcessQueue_Unique_IndexController
    */
    protected function _initAction()
    {
        $module_groupname = $this->getModuleGroupname();
        $module_description = $this->getModuleInstanceDescription();

        $this->loadLayout()
            ->_setActiveMenuValue()
            ->_setSetupTitle(Mage::helper($module_groupname)->__($module_description))
            ->_addBreadcrumb()
            ->_addBreadcrumb(Mage::helper($module_groupname)->__($module_description), Mage::helper($module_groupname)->__($module_description));
            
        return $this;
    }


    /**
    * Standard action on a single task item
    */
    public function actOnTaskAction()
    {
        $_controller_description = $this->getControllerDescription();

        // e.g. reverb_process_queue/task
        $_quote_task = Mage::getModel($this->getModuleGroupname() . '/' . $this->getModuleInstance());

        $task_id = $this->getRequest()->getParam($this->getObjectParamName());
        $_quote_task = $_quote_task->load($task_id);
        if ((!is_object($_quote_task)) || (!$_quote_task->getId())) {
            $error_message = $this->__(self::EXCEPTION_INVALID_TASK_ID, $_controller_description, $task_id);
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        try {
            // This shouldn't fail based on Processor code.
            $this->_getTaskProcessor()->processQueueTask($_quote_task);
        } catch(Exception $e) {
            $error_message = sprintf(self::EXCEPTION_ACT_ON_TASK, $_controller_description, $task_id, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        $action_text = $_quote_task->getActionText();
        $notice_message = sprintf(self::NOTICE_TASK_ACTION, $_controller_description, $task_id, $action_text);
        Mage::getSingleton('adminhtml/session')->addNotice($this->__($notice_message));

        $this->_redirect('*/*/index');
    }
}
