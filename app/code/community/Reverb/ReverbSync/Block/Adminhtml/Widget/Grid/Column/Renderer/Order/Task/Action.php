<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */
class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Task_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    const CONFIRM_TEMPLATE = 'Are you sure you want to manually %s this order sync task?';

    public function render(Varien_Object $row)
    {
        $task_action_text = $row->getActionText();
        if (empty($task_action_text))
        {
            return '';
        }

        $action_array = array();
        $action_array['caption'] = $task_action_text;
        $action_array['confirm'] = sprintf(self::CONFIRM_TEMPLATE, $task_action_text);

        $task_controller = $this->getColumn()->getTaskController();
        $action_url = $this->getUrl('reverbSync/' . $task_controller . '/actOnTask', array('task_id' => $row->getId()));
        $action_array['url'] = $action_url;

        return $this->_toLinkHtml($action_array, $row);
    }
}
