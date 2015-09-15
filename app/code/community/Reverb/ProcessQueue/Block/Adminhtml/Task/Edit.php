<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */

class Reverb_ProcessQueue_Block_Adminhtml_Task_Edit extends Reverb_Base_Block_Adminhtml_Widget_Form_Container
{
    // TODO Implement ACL for modifying tasks
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('delete');
    }
}
