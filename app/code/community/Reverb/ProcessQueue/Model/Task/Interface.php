<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

interface Reverb_ProcessQueue_Model_Task_Interface
{
    public function getId();

    public function getStatus();

    public function attemptUpdatingRowAsProcessing();

    public function selectForUpdate();

    public function executeTask();

    public function setTaskAsCompleted();

    public function setTaskAsErrored();
}