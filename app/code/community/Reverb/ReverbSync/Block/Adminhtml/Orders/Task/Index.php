<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Index
    extends Reverb_ProcessQueue_Block_Adminhtml_Task_Index
{
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'ReverbSync';

        $this->removeButton('add');
    }
} 