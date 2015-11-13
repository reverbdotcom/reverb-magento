<?php

class Reverb_ReverbSync_Block_Adminhtml_Orders_Index extends Mage_Adminhtml_Block_Widget_Container
{
    const LAST_EXECUTED_AT_TEMPLATE = '<h3>The last Sync Task was executed at %s</h3>';

    protected $_outstandingTasksCollection = null;
    protected $_completedAndAllQueueTasks = null;

    protected $_view_html = '';

    protected $_status_to_detail_label_mapping = array(
        Reverb_ProcessQueue_Model_Task::STATUS_PENDING => 'In Progress',
        Reverb_ProcessQueue_Model_Task::STATUS_PROCESSING => 'In Progress',
        Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE => 'Completed',
        Reverb_ProcessQueue_Model_Task::STATUS_ERROR => 'Awaiting Retry',
        Reverb_ProcessQueue_Model_Task::STATUS_ABORTED => 'Failed'
    );

    public function __construct()
    {
        $this->_setHeaderText();
        $block_module_groupname = "ReverbSync";

        $this->_objectId = 'reverb_orders_sync_container';

        parent::__construct();

        $this->setTemplate('ReverbSync/sales/order/index/container.phtml');

        $bulk_orders_sync_process_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_orders_sync/bulkSync', $this->_getBulkSyncUrlParams()),
            'label' => $this->_retrieveAndProcessTasksButtonLabel()
        );

        $process_downloaded_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_orders_sync/syncDownloaded', $this->_getBulkSyncUrlParams()),
            'label' => $this->_processDownloadedTasksButtonLabel()
        );

        $action_buttons_array['bulk_orders_sync'] = $bulk_orders_sync_process_button;
        $action_buttons_array['sync_downloaded_tasks'] = $process_downloaded_tasks_button;

        foreach ($action_buttons_array as $button_id => $button_data)
        {
            $button_action_url = isset($button_data['action_url']) ? $button_data['action_url'] : '';
            if (empty($button_action_url))
            {
                // Require label to be defined
                continue;
            }

            $button_label = isset($button_data['label']) ? $button_data['label'] : '';
            if (empty($button_label))
            {
                // Require label to be defined
                continue;
            }

            $this->_addButton(
                $button_id, array(
                    'label' => Mage::helper($block_module_groupname)->__($button_label),
                    'onclick' => "document.location='" .$button_action_url . "'",
                    'level' => -1
                )
            );
        }
    }

    protected function _retrieveAndProcessTasksButtonLabel()
    {
        return 'Download and Process Order Updates';
    }

    protected function _processDownloadedTasksButtonLabel()
    {
        return 'Process Downloaded Order Updates';
    }

    protected function _setHeaderText()
    {
        list($completed_queue_tasks, $all_process_queue_tasks) = $this->_getCompletedAndAllQueueTasks();

        $completed_tasks_count = count($completed_queue_tasks);
        $all_tasks_count = count($all_process_queue_tasks);
        $header_text = Mage::helper('ReverbSync')->__($this->_getHeaderTextTemplate(), $completed_tasks_count, $all_tasks_count);
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
                                    // This case should never occur, but if it doesn, it's likely because something went
                                    // very wrong with the task's execution
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
        $last_sync_message = sprintf(self::LAST_EXECUTED_AT_TEMPLATE, $locale_most_recent_executed_at_date);
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
                                                        ->getCompletedAndAllQueueTasks($this->_getTaskCode());
        }

        return $this->_completedAndAllQueueTasks;
    }

    protected function _getOutstandingTasksCollection()
    {
        if (is_null($this->_outstandingTasksCollection))
        {
            $this->_outstandingTasksCollection = $this->_getTaskProcessorHelper()
                                                        ->getQueueTasksForProgressScreen($this->_getTaskCode());
        }

        return $this->_outstandingTasksCollection;
    }

    protected function _getBulkSyncUrlParams()
    {
        return array();
    }

    protected function _getHeaderTextTemplate()
    {
        return '%s of %s Reverb Order Update Tasks have completed syncing with Magento';
    }

    protected function _getTaskCode()
    {
        return 'order_update';
    }

    protected function _getTaskProcessorHelper()
    {
        return Mage::helper('reverb_process_queue/task_processor');
    }

    public function getViewHtml()
    {
        return $this->_view_html;
    }
}
