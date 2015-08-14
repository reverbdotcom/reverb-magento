<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 * Class Reverb_ProcessQueue_Model_Mysql_Task
 */

class Reverb_ProcessQueue_Model_Mysql4_Task extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('reverb_process_queue/task','task_id');
    }

    public function selectForUpdate(Reverb_ProcessQueue_Model_Task_Interface $taskObject)
    {
        $task_id = $taskObject->getId();
        if (empty($task_id))
        {
            // $taskObject must be an existing/loaded object in order to lock it
            return false;
        }

        $select = $this->_getWriteAdapter()->select()
                        ->from(array('process_queue' => $this->getMainTable()))
                        ->where('task_id=?', $task_id)
                        ->where('status=?', Reverb_ProcessQueue_Model_Task::STATUS_PROCESSING)
                        ->forUpdate(true);

        $selected = $this->_getWriteAdapter()->fetchOne($select);
        return $selected;
    }

    public function attemptUpdatingRowAsProcessing(Reverb_ProcessQueue_Model_Task_Interface $taskObject)
    {
        $task_id = $taskObject->getId();
        if (empty($task_id))
        {
            // $taskObject must be an existing/loaded object in order to lock it
            return false;
        }
        // Status here can be PENDING or ERROR
        $current_status = $taskObject->getStatus();
        $current_gmt_datetime = Mage::getSingleton('core/date')->gmtDate();

        // First, attempt to update the row based on id and status. If no rows are updated, another thread has already
        //  begun processing this row. Also we want to do this outside of any transactions so that we know other mysql
        //  connections will see that this row is already processing

        $update_bind_array = array('status' => Reverb_ProcessQueue_Model_Task::STATUS_PROCESSING,
                                    'last_executed_at' => $current_gmt_datetime);
        $where_conditions_array = array('task_id=?' => $task_id, 'status=?' => $current_status);

        $rows_updated = $this->_getWriteAdapter()->update($this->getMainTable(), $update_bind_array, $where_conditions_array);
        return $rows_updated;
    }

    public function setTaskAsCompleted(Reverb_ProcessQueue_Model_Task_Interface $taskObject)
    {
        $task_id = $taskObject->getId();
        if (empty($task_id))
        {
            return false;
        }

        $update_bind_array = array('status' => Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE);
        $where_conditions_array = array('task_id=?' => $task_id);
        $rows_updated = $this->_getWriteAdapter()->update($this->getMainTable(), $update_bind_array, $where_conditions_array);
        return $rows_updated;
    }

    public function setTaskAsErrored(Reverb_ProcessQueue_Model_Task_Interface $taskObject)
    {
        $task_id = $taskObject->getId();
        if (empty($task_id))
        {
            return false;
        }

        $update_bind_array = array('status' => Reverb_ProcessQueue_Model_Task::STATUS_ERROR);
        $where_conditions_array = array('task_id=?' => $task_id);
        $rows_updated = $this->_getWriteAdapter()->update($this->getMainTable(), $update_bind_array, $where_conditions_array);
        return $rows_updated;
    }
}
