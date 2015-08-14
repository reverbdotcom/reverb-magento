<?php
/**
 * Author: Sean Reverb
 * Created: 8/14/15
 */

class Reverb_ProcessQueue_Model_Task_Result
    implements Reverb_ProcessQueue_Model_Task_Result_Interface
{
    protected $_task_status = null;

    /**
     * To be used when an execution of a callback in Reverb_ProcessQueue_Model_Task::executeTask() does not return
     *      an object of type Reverb_ProcessQueue_Model_Task_Result
     */
    protected $_methodCallbackResult = null;

    public function getMethodCallbackResult()
    {
        return $this->_methodCallbackResult;
    }

    public function setMethodCallbackResult($methodCallbackResult)
    {
        $this->_methodCallbackResult = $methodCallbackResult;
        return $this;
    }

    /**
     * Returns the status of a Reverb_ProcessQueue_Model_Task::executeTask() call
     *
     * @return mixed - Expected to return one of the STATUS_* constants in class Reverb_ProcessQueue_Model_Task
     */
    public function getTaskStatus()
    {
        return $this->_task_status;
    }

    /**
     * Sets the status of a Reverb_ProcessQueue_Model_Task::executeTask() call
     *
     * @return mixed - Expected to pass in one of the STATUS_* constants in class Reverb_ProcessQueue_Model_Task
     */
    public function setTaskStatus($task_status)
    {
        $this->_task_status = $task_status;
        return $this;
    }
}
