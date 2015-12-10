<?php

class Reverb_ReverbSync_Block_Adminhtml_Listings_Index extends Mage_Adminhtml_Block_Widget_Container
{
    const LAST_EXECUTED_AT_TEMPLATE = '<h3>The last Reverb Listing Sync was executed at %s</h3>';

    protected $_view_html = '';

    public function __construct()
    {
        $this->_setHeaderText();
        $block_module_groupname = "ReverbSync";

        $this->_objectId = 'reverb_product_sync_container';
        $this->setTemplate('widget/view/container.phtml');

        parent::__construct();

        $bulk_sync_process_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_listings_sync/bulkSync'),
            'label' => 'Bulk Product Sync'
        );

        $clear_all_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_listings_sync/clearAllTasks'),
            'label' => 'Clear All Sync Tasks'
        );

        $clear_successful_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_listings_sync/clearSuccessfulTasks'),
            'label' => 'Clear Successful Sync Tasks'
        );

        $action_buttons_array['bulk_product_sync'] = $bulk_sync_process_button;
        $action_buttons_array['clear_all_sync_tasks'] = $clear_all_tasks_button;
        $action_buttons_array['clear_successful_sync_tasks'] = $clear_successful_tasks_button;

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
        $this->_headerText = "Sync Products With Reverb";

        list($completed_queue_tasks, $all_process_queue_tasks) =
            Mage::helper('reverb_process_queue/task_processor')->getCompletedAndAllQueueTasks('listing_sync');

        $mostRecentExecutedTask = reset($all_process_queue_tasks);

        if (!is_object($mostRecentExecutedTask))
        {
            return;
        }

        $gmt_most_recent_executed_at_date = $mostRecentExecutedTask->getLastExecutedAt();
        $locale_most_recent_executed_at_date = Mage::getSingleton('core/date')
                                                    ->date(null, $gmt_most_recent_executed_at_date);
        $last_sync_message = sprintf(self::LAST_EXECUTED_AT_TEMPLATE, $locale_most_recent_executed_at_date);
        $this->_view_html = $last_sync_message;
    }

    public function getViewHtml()
    {
        return $this->_view_html;
    }
}
