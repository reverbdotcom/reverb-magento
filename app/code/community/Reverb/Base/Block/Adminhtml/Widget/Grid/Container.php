<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

class Reverb_Base_Block_Adminhtml_Widget_Grid_Container
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $controllerAction = $this->getAction();
        $module_groupname = $controllerAction->getModuleGroupname();
        $module_instance_description = $controllerAction->getModuleInstanceDescription();

        $this->_blockGroup = $module_groupname;
        $this->_controller = $controllerAction->getIndexBlockName();
        $this->_headerText = Mage::helper($module_groupname)->__($module_instance_description);
        parent::__construct();

        $action_buttons_array = $this->getActionButtonsToRender();

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
                    'label' => Mage::helper($module_groupname)->__($button_label),
                    'onclick' => "document.location='" .$button_action_url . "'",
                    'level' => -1
                )
            );
        }
    }


    // OPTIONAL

    // Subclass may override this class
    public function getActionButtonsToRender()
    {
        return array();
    }
}
