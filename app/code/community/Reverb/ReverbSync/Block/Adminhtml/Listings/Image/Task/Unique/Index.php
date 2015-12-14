<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Listings_Image_Task_Unique_Index
    extends Reverb_ProcessQueue_Block_Adminhtml_Task_Unique_Index
{
    const IMAGE_LISTING_SYNC_TASK_CODE = 'listing_image_sync';

    public function __construct()
    {
        $controllerAction = $this->getAction();
        $module_groupname = $controllerAction->getModuleGroupname();
        $module_instance_description = $controllerAction->getModuleInstanceDescription();

        $this->_blockGroup = $module_groupname;
        $this->_controller = $controllerAction->getIndexBlockName();
        $this->_headerText = Mage::helper($module_groupname)->__($module_instance_description);
        parent::__construct();

        $this->_removeButton('add');
        $this->_blockGroup = $this->getAction()->getBlockModuleGroupname();
    }

    public function getTaskJobCodes()
    {
        return array(self::IMAGE_LISTING_SYNC_TASK_CODE);
    }

    public function getTaskCodeToFilterBy()
    {
        return self::IMAGE_LISTING_SYNC_TASK_CODE;
    }

    protected function _expediteTasksButtonLabel()
    {
        return 'Expedite Image Sync Tasks';
    }

    protected function _getHeaderTextTemplate()
    {
        return '%s of %s Listings Image Sync Tasks have completed syncing';
    }
}
