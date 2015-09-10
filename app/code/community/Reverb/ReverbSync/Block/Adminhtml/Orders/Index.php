<?php

class Reverb_ReverbSync_Block_Adminhtml_Orders_Index extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        $this->_headerText = "Sync Orders With Reverb";
        $block_module_groupname = "ReverbSync";

        $this->_objectId = 'reverb_orders_sync_container';
        $this->setTemplate('widget/form/container.phtml');

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
}
