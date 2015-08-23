<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ProcessQueue_Model_Mysql4_Task_Unique
    extends Reverb_ProcessQueue_Model_Mysql4_Task
{
    public function _construct()
    {
        $this->_init('reverb_process_queue/task_unique', 'task_id');
    }
}
