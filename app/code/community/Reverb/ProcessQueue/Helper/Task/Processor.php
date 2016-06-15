<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 * Class Reverb_ProcessQueue_Helper_Processor
 */

class Reverb_ProcessQueue_Helper_Task_Processor extends Mage_Core_Helper_Data
{
    const EXCEPTION_UPDATE_AS_PROCESSING = 'Error attempting to update queue task with id %s as processing: %s';
    const EXCEPTION_SELECT_FOR_UPDATE = 'Error attempting to select queue task with id %s for update: %s';
    const ERROR_FAILED_TO_SELECT_FOR_UPDATE = 'Failed to select queue task with id %s for update';
    const EXCEPTION_EXECUTING_TASK = 'Error executing task for queue task object with id %s: %s';
    const EXCEPTION_ACTING_ON_TASK_RESULT = 'Error acting on the task result for task with id %s: %s';
    const EXCEPTION_COMMITTING_TRANSACTION = 'An uncaught exception occurred when attempting to commit the transaction for process queue object with id %s: %s';

    protected $_moduleName = 'reverb_process_queue';
    protected $_task_model_classname = 'reverb_process_queue/task';
    protected $_taskResourceSingleton = null;
    protected $_logModel = null;

    // TODO Refactor this
    protected $_batch_size = 2500;

    // TODO Create separate database connection for queue task resource Singleton
    public function processQueueTasks($code = null)
    {
        $processQueueTaskCollection = $this->getQueueTasksForProcessing($code);

        // Update the last_executed_at value for these task rows so that the next cron iteration will pick up a different
        //  set of BATCH_SIZE rows from the call to $this->getQueueTasksForProcessing($code); above
        $this->updateLastExecutedAtToCurrentTime($processQueueTaskCollection);

        foreach ($processQueueTaskCollection as $processQueueTaskObject)
        {
            $this->processQueueTask($processQueueTaskObject);
        }
    }

    /**
     * Executes the following:
     *  - Attempts to update task object's row as processing
     *  - If successful, Begins a database transaction
     *  - Attempts to select that row for update
     *  - If successful, attempts to execute method callback defined in row, returning a result object
     *  - Updates the task object based on the resulting object
     *  - Commits the database transaction
     *
     * @param Reverb_ProcessQueue_Model_Task_Interface $processQueueTaskObject
     */
    public function processQueueTask(Reverb_ProcessQueue_Model_Task_Interface $processQueueTaskObject)
    {
        try
        {
            $able_to_lock_for_processing = $processQueueTaskObject->attemptUpdatingRowAsProcessing();
            if (!$able_to_lock_for_processing)
            {
                // Assume another thread of execution is already processing this task
                return;
            }
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_UPDATE_AS_PROCESSING, $processQueueTaskObject->getId(), $e->getMessage());
            $this->_logError($error_message);
            return;
        }

        // At this point, start transaction and lock row for update to ensure exclusive access
        $taskResourceSingleton = $processQueueTaskObject->getResource();
        $taskResourceSingleton->beginTransaction();
        try
        {
            $selected = $processQueueTaskObject->selectForUpdate();
            if (!$selected)
            {
                // Assume another thread has already locked this task object's row, although this shouldn't happen
                $taskResourceSingleton->rollBack();
                $error_message = $this->__(self::ERROR_FAILED_TO_SELECT_FOR_UPDATE, $processQueueTaskObject->getId());
                $this->_logError($error_message);

                return;
            }
        }
        catch(Exception $e)
        {
            $taskResourceSingleton->rollBack();
            $error_message = $this->__(self::EXCEPTION_SELECT_FOR_UPDATE, $processQueueTaskObject->getId(), $e->getMessage());
            $this->_logError($error_message);
            return;
        }

        try
        {
            $taskExecutionResult = $processQueueTaskObject->executeTask();
        }
        catch(Exception $e)
        {
            $taskResourceSingleton->rollBack();
            $error_message = $this->__(self::EXCEPTION_EXECUTING_TASK, $processQueueTaskObject->getId(), $e->getMessage());
            $processQueueTaskObject->setTaskAsErrored($error_message);
            $this->_logError($error_message);
            return;
        }

        try
        {
            $processQueueTaskObject->actOnTaskResult($taskExecutionResult);
        }
        catch(Exception $e)
        {
            // At this point, we would assume that the task has been performed successfully since executeTask() did not
            //  throw any exceptions. As such, log the exception but commit the transaction. Even if this leaves a row
            //  in the PROCESSING state, it's better than leaving parts of the database out of sync with external resources
            $error_message = $this->__(self::EXCEPTION_ACTING_ON_TASK_RESULT, $processQueueTaskObject->getId(), $e->getMessage());
            $this->_logError($error_message);
        }

        try
        {
            $taskResourceSingleton->commit();
        }
        catch(Exception $e)
        {
            // If an exception occurs here, rollback
            $taskResourceSingleton->rollback();
            $processQueueTaskObject->setTaskAsErrored();
            $error_message = $this->__(self::EXCEPTION_COMMITTING_TRANSACTION, $processQueueTaskObject->getId(), $e->getMessage());
            $this->_logError($error_message);
        }
    }

    public function getQueueTasksForProcessing($code = null)
    {
        $processQueueTaskCollection = $this->_getTaskCollectionModel()
                                        ->addOpenForProcessingFilter()
                                        ->sortByLeastRecentlyExecuted()
                                        ->setPageSize($this->_batch_size);

        if (!empty($code))
        {
            $processQueueTaskCollection->addCodeFilter($code);
        }

        return $processQueueTaskCollection;
    }

    public function getQueueTasksForProgressScreen($code = null)
    {
        $processQueueTaskCollection = $this->_getTaskCollectionModel()
                                        ->addOpenForProcessingFilter()
                                        ->sortByLeastRecentlyExecuted()
                                        ->setPageSize($this->_batch_size);

        if (!empty($code))
        {
            $processQueueTaskCollection->addCodeFilter($code);
        }

        return $processQueueTaskCollection;
    }

    public function getCompletedAndAllQueueTasks($code = null)
    {
        $allProcessQueueTaskCollection = $this->_getTaskCollectionModel()
                                                ->setOrder('last_executed_at', Zend_Db_Select::SQL_DESC);;

        if (!empty($code))
        {
            $allProcessQueueTaskCollection->addCodeFilter($code);
        }

        $all_process_queue_tasks = $allProcessQueueTaskCollection->getItems();

        $completedTasksCollection = $this->_getTaskCollectionModel()
                                            ->addStatusFilter(Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE)
                                            ->setOrder('last_executed_at', Zend_Db_Select::SQL_DESC);

        if (!empty($code))
        {
            $completedTasksCollection->addCodeFilter($code);
        }

        $completed_queue_tasks = $completedTasksCollection->getItems();

        return array($completed_queue_tasks, $all_process_queue_tasks);
    }

    public function updateLastExecutedAtToCurrentTime(Reverb_ProcessQueue_Model_Mysql4_Task_Collection $processQueueTaskCollection)
    {
        $task_objects_array = $processQueueTaskCollection->getItems();
        $task_ids = array();
        foreach ($task_objects_array as $taskObject)
        {
            $task_ids[] = $taskObject->getTaskId();
        }
        $rows_updated = $processQueueTaskCollection->getResource()->updateLastExecutedAtToCurrentTime($task_ids);
        return $rows_updated;
    }

    public function deleteAllTasks($task_codes)
    {
        $rows_deleted = $this->_getTaskResourceSingleton()->deleteAllTasks($task_codes);
        return $rows_deleted;
    }

    public function deleteSuccessfulTasks($task_codes)
    {
        $rows_deleted = $this->_getTaskResourceSingleton()->deleteSuccessfulTasks($task_codes);
        return $rows_deleted;
    }

    protected function _getTaskCollectionModel()
    {
        return Mage::getModel($this->_task_model_classname)->getCollection();
    }

    /**
     * @return Reverb_ProcessQueue_Model_Mysql4_Task
     */
    protected function _getTaskResourceSingleton()
    {
        if (is_null($this->_taskResourceSingleton))
        {
            $this->_taskResourceSingleton = Mage::getResourceSingleton($this->_task_model_classname);
        }

        return $this->_taskResourceSingleton;
    }

    protected function _logError($error_message)
    {
        $exceptionToLog = new Exception($error_message);
        $this->_getLogModel()->logQueueProcessorException($exceptionToLog);
    }

    protected function _getLogModel()
    {
        if (is_null($this->_logModel))
        {
            $this->_logModel = Mage::getSingleton('reverb_process_queue/log');
        }

        return $this->_logModel;
    }
}
