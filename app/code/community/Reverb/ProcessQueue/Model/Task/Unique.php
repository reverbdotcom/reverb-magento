<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ProcessQueue_Model_Task_Unique
    extends Reverb_ProcessQueue_Model_Task
    implements Reverb_ProcessQueue_Model_Task_Interface
{
    protected function _construct()
    {
        $this->_init('reverb_process_queue/task_unique');
    }
}
