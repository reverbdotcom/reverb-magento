<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */

class Reverb_ProcessQueue_Block_Adminhtml_Task_Index
    extends Reverb_Base_Block_Adminhtml_Widget_Grid_Container
{
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

    public function getActionButtonsToRender()
    {
        $buttons_to_render = parent::getActionButtonsToRender();

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

        $clear_all_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ProcessQueue_index/clearAllTasks',
                                                                        array('task_codes' => $task_code_param,
                                                                        'redirect_route' => $encoded_redirect_route)
                                                                    ),
            'label' => 'Clear All Tasks'
        );

        $clear_successful_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ProcessQueue_index/clearSuccessfulTasks',
                                                                        array('task_codes' => $task_code_param,
                                                                        'redirect_route' => $encoded_redirect_route)
                                                                    ),
            'label' => 'Clear Successful Sync Tasks'
        );

        $buttons_to_render['clear_all_sync_tasks'] = $clear_all_tasks_button;
        $buttons_to_render['clear_successful_sync_tasks'] = $clear_successful_tasks_button;

        return $buttons_to_render;
    }
}
