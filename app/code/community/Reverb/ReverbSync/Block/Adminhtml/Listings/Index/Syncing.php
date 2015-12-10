<?php

class Reverb_ReverbSync_Block_Adminhtml_Listings_Index_Syncing extends Mage_Adminhtml_Block_Widget_Container
{
    const HEADER_TEXT_TEMPLATE = '%s of %s product listings have completed syncing with Reverb';

    public function __construct()
    {
        $this->_setHeaderText();
        $block_module_groupname = "ReverbSync";

        $this->_objectId = 'reverb_stop_product_sync_container';
        $this->setTemplate('widget/view/container.phtml');

        parent::__construct();

        $bulk_sync_process_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_listings_sync/stopBulkSync'),
            'label' => 'Stop Bulk Sync'
        );

        $clear_all_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_listings_sync/clearAllTasks'),
            'label' => 'Clear All Sync Tasks'
        );

        $clear_successful_tasks_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('adminhtml/ReverbSync_listings_sync/clearSuccessfulTasks'),
            'label' => 'Clear Successful Sync Tasks'
        );

        $action_buttons_array['clear_all_sync_tasks'] = $clear_all_tasks_button;
        $action_buttons_array['clear_successful_sync_tasks'] = $clear_successful_tasks_button;
        $action_buttons_array['bulk_product_sync'] = $bulk_sync_process_button;

        foreach ($action_buttons_array as $button_id => $button_data)
        {
            $button_action_url = isset($button_data['action_url']) ? $button_data['action_url'] : '';
            if (empty($button_action_url))
            {
                // Url must be defined
                continue;
            }

            $button_label = isset($button_data['label']) ? $button_data['label'] : '';
            if (empty($button_label))
            {
                // Label must be defined
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
            Mage::helper('reverb_process_queue/task_processor')->getCompletedAndAllQueueTasks('listing_sync');

        $completed_tasks_count = count($completed_queue_tasks);
        $all_tasks_count = count($all_process_queue_tasks);
        $header_text = Mage::helper('ReverbSync')->__(self::HEADER_TEXT_TEMPLATE, $completed_tasks_count, $all_tasks_count);
        $this->_headerText = $header_text;
    }
}
