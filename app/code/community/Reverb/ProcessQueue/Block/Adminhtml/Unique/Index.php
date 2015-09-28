<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */

abstract class Reverb_ProcessQueue_Block_Adminhtml_Unique_Index
    extends Reverb_ProcessQueue_Block_Adminhtml_Index
{
    protected function _getTaskProcessorHelper()
    {
        return Mage::helper('reverb_process_queue/task_processor_unique');
    }
}
