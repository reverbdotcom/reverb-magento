<?php

$installer  = $this;
$installer->startSetup();

// The cronjob name was refactored once multiple crons were needed for this module. The point of this script is to
// remove the deprecated cronjob name to prevent errors from being thrown

$cronResourceSingleton = Mage::getResourceSingleton('cron/schedule');

$where_condition_array = array('job_code=?' => 'reverb_process_queue_process_tasks', 'status=?' => 'pending');
$rows_deleted = $this->getConnection()->delete($cronResourceSingleton->getMainTable(), $where_condition_array);

$installer->endSetup();
