<?php

class Reverb_ReverbSync_Block_Adminhtml_Index extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        $this->_headerText = "Sync Products With Reverb";
        $block_module_groupname = $this->getAction()->getModuleBlockGroupname();

        $this->_objectId = 'reverb_product_sync_container';
        $this->setTemplate('widget/form/container.phtml');

        parent::__construct();

        $bulk_sync_process_button = array(
            'action_url' => Mage::getModel('adminhtml/url')->getUrl('reverbSync/adminhtml_sync/bulkSync'),
            'label' => 'Bulk Product Sync'
        );

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
}
