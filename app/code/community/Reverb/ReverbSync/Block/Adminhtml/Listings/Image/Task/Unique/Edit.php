<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Listings_Image_Task_Unique_Edit
    extends Reverb_ProcessQueue_Block_Adminhtml_Task_Unique_Edit
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_listings_image_task_unique';
        $this->_blockGroup = 'ReverbSync';

        $this->_removeButton('delete');

        if (!$this->getAction()->canAdminUpdateStatus())
        {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        }
    }
}
