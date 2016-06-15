<?php
/**
 * The purpose of this migration is to remove all COMPLETE/ABORTED tasks of type `order_creation`. Orders will
 *  now be created via the order update process. We don't want to remove any `order_creation` tasks which are in state
 *  PROCESSING as those tasks are currently being executed. Tasks in states PENDING or ERROR may have been selected by
 *  a cron process and may be queued to be processed, so we won't want to interrupt those tasks either.
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$statuses_to_delete = array(Reverb_ProcessQueue_Model_Task::STATUS_ABORTED, Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE);

Mage::getResourceSingleton('reverb_process_queue/task_unique')->deleteAllTasks('order_creation', $statuses_to_delete);

$installer->endSetup();
