<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */

abstract class Reverb_ProcessQueue_Block_Adminhtml_Index
    extends Mage_Adminhtml_Block_Widget_Container
{
    abstract public function getTaskCodeToFilterBy();

    protected $_outstandingTasksCollection = null;
    protected $_completedAndAllQueueTasks = null;

    protected $_status_to_detail_label_mapping = array(
        Reverb_ProcessQueue_Model_Task::STATUS_PENDING => 'In Progress',
        Reverb_ProcessQueue_Model_Task::STATUS_PROCESSING => 'In Progress',
        Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE => 'Completed',
        Reverb_ProcessQueue_Model_Task::STATUS_ERROR => 'Awaiting Retry',
        Reverb_ProcessQueue_Model_Task::STATUS_ABORTED => 'Failed'
    );

    protected function _getLastExecutedAtTemplate()
    {
        return '<h3>The last Sync Task was executed at %s</h3>';
    }

    public function __construct()
    {
        $this->_setHeaderText();

        $this->_objectId = 'reverb_processqueue_task_index_container';

        parent::__construct();

        $this->_controller = $this->getAction()->getIndexBlockName();

        $this->setTemplate('ReverbSync/processqueue/task/index/container.phtml');

        $expedite_tasks_button = array(
            'action_url' => $this->_getExpediteTasksButtonActionUrl(),
            'label' => $this->_expediteTasksButtonLabel()
        );

        $action_buttons_array = array();
        //$action_buttons_array['expedite_tasks'] = $expedite_tasks_button;

        foreach ($action_buttons_array as $button_id => $button_data)
        {
            $button_action_url = isset($button_data['action_url']) ? $button_data['action_url'] : '';
            $button_label = isset($button_data['label']) ? $button_data['label'] : '';

            $this->_addButton(
                $button_id, array(
                    'label' => Mage::helper($this->getAction()->getModuleGroupname())->__($button_label),
                    'onclick' => "document.location='" .$button_action_url . "'",
                    'level' => -1
                )
            );
        }
    }

    protected function _setHeaderText()
    {
        list($completed_queue_tasks, $all_process_queue_tasks) = $this->_getCompletedAndAllQueueTasks();

        $completed_tasks_count = count($completed_queue_tasks);
        $all_tasks_count = count($all_process_queue_tasks);
        $header_text = Mage::helper('reverb_process_queue')
            ->__($this->_getHeaderTextTemplate(), $completed_tasks_count, $all_tasks_count);
        $this->_headerText = $header_text;
    }

    public function getTaskCountsByStatusDetailLabel()
    {
        list($completed_queue_tasks, $all_process_queue_tasks) = $this->_getCompletedAndAllQueueTasks();

        $task_counts_by_status_detail = array();
        // Initialize all labels as having 0 tasks
        foreach ($this->_status_to_detail_label_mapping as $status => $status_detail_label)
        {
            $task_counts_by_status_detail[$status_detail_label] = 0;
        }

        foreach ($all_process_queue_tasks as $task)
        {
            $status = $task->getStatus();
            $status_detail_label = isset($this->_status_to_detail_label_mapping[$status])
                ? $this->_status_to_detail_label_mapping[$status]
                // This case should never occur, but if it does, it's likely because something went
                //      very wrong with the task's execution
                : $this->_status_to_detail_label_mapping[Reverb_ProcessQueue_Model_Task::STATUS_ABORTED];

            $task_counts_by_status_detail[$status_detail_label] = $task_counts_by_status_detail[$status_detail_label] + 1;
        }

        return $task_counts_by_status_detail;
    }

    public function getMostRecentTaskMessaging()
    {
        list($completed_queue_tasks, $all_process_queue_tasks) = $this->_getCompletedAndAllQueueTasks();
        $mostRecentTask = reset($all_process_queue_tasks);
        if (!is_object($mostRecentTask))
        {
            return '';
        }

        $gmt_most_recent_executed_at_date = $mostRecentTask->getLastExecutedAt();
        $locale_most_recent_executed_at_date = Mage::getSingleton('core/date')->date(null, $gmt_most_recent_executed_at_date);
        $last_sync_message = sprintf($this->_getLastExecutedAtTemplate(), $locale_most_recent_executed_at_date);
        return $last_sync_message;
    }

    public function areTasksOutstanding()
    {
        $outstandingTasksCollection = $this->_getOutstandingTasksCollection();
        return ($outstandingTasksCollection->count() > 0);
    }

    protected function _getCompletedAndAllQueueTasks()
    {
        if (is_null($this->_completedAndAllQueueTasks))
        {
            $this->_completedAndAllQueueTasks = $this->_getTaskProcessorHelper()
                ->getCompletedAndAllQueueTasks($this->getTaskCodeToFilterBy());
        }

        return $this->_completedAndAllQueueTasks;
    }

    protected function _getOutstandingTasksCollection()
    {
        if (is_null($this->_outstandingTasksCollection))
        {
            $this->_outstandingTasksCollection = $this->_getTaskProcessorHelper()
                ->getQueueTasksForProgressScreen($this->getTaskCodeToFilterBy());
        }

        return $this->_outstandingTasksCollection;
    }

    protected function _getHeaderTextTemplate()
    {
        return '%s of %s Tasks have completed processing';
    }

    protected function _getExpediteTasksButtonActionUrl()
    {
        return $this->getAction()->getUriPathForAction('expedite');
    }

    protected function _getTaskProcessorHelper()
    {
        return Mage::helper('reverb_process_queue/task_processor');
    }

    protected function _expediteTasksButtonLabel()
    {
        return 'Expedite Tasks';
    }
}
