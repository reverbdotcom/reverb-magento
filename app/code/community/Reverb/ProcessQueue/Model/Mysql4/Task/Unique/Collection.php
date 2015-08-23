<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 * Class Reverb_ProcessQueue_Model_Mysql_Task_Collection
 */

class Reverb_ProcessQueue_Model_Mysql4_Task_Unique_Collection extends Reverb_ProcessQueue_Model_Mysql4_Task_Collection
{
    protected function _construct()
    {
        $this->_init('reverb_process_queue/task_unique');
    }
}
