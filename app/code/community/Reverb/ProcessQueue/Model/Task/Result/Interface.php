<?php
/**
 * Author: Sean Reverb
 * Created: 8/14/15
 */

interface Reverb_ProcessQueue_Model_Task_Result_Interface
{
    /**
     * Returns the status of a Reverb_ProcessQueue_Model_Task::executeTask() call
     *
     * @return mixed - Expected to return one of the STATUS_* constants in class Reverb_ProcessQueue_Model_Task
     */
    public function getTaskStatus();

    /**
     * Sets the status of a Reverb_ProcessQueue_Model_Task::executeTask() call
     *
     * @return mixed - Expected to pass in one of the STATUS_* constants in class Reverb_ProcessQueue_Model_Task
     */
    public function setTaskStatus($status);

    /**
     * Returns any messaging resulting from a Reverb_ProcessQueue_Model_Task::executeTask() call
     *
     * @return mixed - string|null
     */
    public function getTaskStatusMessage();

    /**
     * Sets any messaging resulting from a Reverb_ProcessQueue_Model_Task::executeTask() call
     *
     * @return $this
     */
    public function setTaskStatusMessage($status);

    /**
     * To be used when an execution of a callback in Reverb_ProcessQueue_Model_Task::executeTask() does not return
     *      an object of type Reverb_ProcessQueue_Model_Task_Result
     */
    public function getMethodCallbackResult();

    /**
     * To be used when an execution of a callback in Reverb_ProcessQueue_Model_Task::executeTask() does not return
     *      an object of type Reverb_ProcessQueue_Model_Task_Result
     */
    public function setMethodCallbackResult($methodCallbackResult);
}