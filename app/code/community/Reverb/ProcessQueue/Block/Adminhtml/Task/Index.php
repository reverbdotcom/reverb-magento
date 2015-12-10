<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */

class Reverb_ProcessQueue_Block_Adminhtml_Task_Index
    extends Reverb_Base_Block_Adminhtml_Widget_Grid_Container
{
    const BUTTON_ACTION_TEMPLATE = "confirmSetLocation('%s', '%s')";

    /**
     * Should return the task job codes for this page
     *
     * @return string
     */
    public function getTaskJobCodes()
    {
        return null;
    }

    public function getRedirectRoute()
    {
        return $this->getAction()->getFullBackControllerActionPath();
    }

    public function __construct()
    {
        parent::__construct();

        $this->_addClearTasksButtons();
    }

    public function _addClearTasksButtons()
    {
        $buttons_to_render = array();

        $task_codes = $this->getTaskJobCodes();
        if (is_array($task_codes) && (!empty($task_codes)))
        {
            $imploded_task_codes = implode(';', $task_codes);
            $task_code_param = urlencode($imploded_task_codes);
        }
        else
        {
            $task_code_param = null;
        }
        $encoded_redirect_route = urlencode($this->getRedirectRoute());

        $clear_all_tasks_action = $this->getAction()->getUriPathForIndexAction('clearAllTasks');

        $clear_all_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl($clear_all_tasks_action,
                                                                        array('task_codes' => $task_code_param,
                                                                        'redirect_route' => $encoded_redirect_route)
                                                                    ),
            'label' => 'Clear All Tasks',
            'confirm_message' => 'Are you sure you want to clear all tasks?'
        );

        $clear_successful_tasks_action = $this->getAction()->getUriPathForIndexAction('clearSuccessfulTasks');
        $clear_successful_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl($clear_successful_tasks_action,
                                                                        array('task_codes' => $task_code_param,
                                                                        'redirect_route' => $encoded_redirect_route)
                                                                    ),
            'label' => 'Clear Successful Sync Tasks',
            'confirm_message' => 'Are you sure you want to clear all successful tasks?'
        );

        $buttons_to_render['clear_all_sync_tasks'] = $clear_all_tasks_button;
        $buttons_to_render['clear_successful_sync_tasks'] = $clear_successful_tasks_button;

        foreach ($buttons_to_render as $button_id => $button)
        {
            $label = $this->getAction()->getModuleHelper()->__($button['label']);
            $confirm_message = $this->getAction()->getModuleHelper()->__($button['confirm_message']);
            $action_url = $button['action_url'];
            $onclick = sprintf(self::BUTTON_ACTION_TEMPLATE, $confirm_message, $action_url);

            $this->_addButton(
                $button_id, array(
                    'label' => $this->getAction()->getModuleHelper()->__($label),
                    'onclick' => $onclick,
                    'level' => -1
                )
            );
        }
    }
}
