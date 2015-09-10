<?php

class Reverb_ReverbSync_Block_Adminhtml_Orders_Index extends Mage_Adminhtml_Block_Widget_Container
{
    const HEADER_TEXT_TEMPLATE = '%s of %s Reverb Orders have completed syncing with Magento';
    const LAST_EXECUTED_AT_TEMPLATE = '<h3>The last Reverb Order Sync was executed at %s</h3>';

    protected $_view_html = '';

    public function __construct()
    {
        $this->_setHeaderText();
        $block_module_groupname = "ReverbSync";

        $this->_objectId = 'reverb_orders_sync_container';
        $this->setTemplate('widget/view/container.phtml');

        parent::__construct();

        $bulk_orders_sync_process_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('reverbSync/adminhtml_orders_sync/bulkSync'),
            'label' => 'Bulk Orders Sync'
        );

        $action_buttons_array['bulk_orders_sync'] = $bulk_orders_sync_process_button;

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

    protected function _setHeaderText()
    {
        list($completed_queue_tasks, $all_process_queue_tasks) =
            Mage::helper('reverb_process_queue/task_processor_unique')->getCompletedAndAllQueueTasks('order_creation');

        $outstandingOrdersSyncTasksCollection = Mage::helper('reverb_process_queue/task_processor_unique')
                                                    ->getQueueTasksForProgressScreen('order_creation');
        $outstanding_tasks = $outstandingOrdersSyncTasksCollection->getItems();
        $outstanding_tasks_remaining = count($outstanding_tasks);

        $completed_tasks_count = count($completed_queue_tasks);
        $all_tasks_count = count($all_process_queue_tasks);
        $header_text = Mage::helper('ReverbSync')->__(self::HEADER_TEXT_TEMPLATE, $completed_tasks_count, $all_tasks_count);
        $this->_headerText = $header_text;

        if ($outstanding_tasks_remaining == 0)
        {
            $mostRecentTask = reset($all_process_queue_tasks);
            $gmt_most_recent_executed_at_date = $mostRecentTask->getLastExecutedAt();
            $locale_most_recent_executed_at_date = Mage::getSingleton('core/date')
                                                    ->date(null, $gmt_most_recent_executed_at_date);
            $last_sync_message = sprintf(self::LAST_EXECUTED_AT_TEMPLATE, $locale_most_recent_executed_at_date);
            $this->_view_html = $last_sync_message;
        }
    }

    public function getViewHtml()
    {
        return $this->_view_html;
    }
}
