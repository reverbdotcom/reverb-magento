<?php

$installer  = $this;
$installer->startSetup();

// The cronjob name was refactored to execute order update syncs a minute after order creation syncs. The job codes
// for the crontab tasks were updated/modified as a result, so we want to remove any deprecated crontab job codes

$cronResourceSingleton = Mage::getResourceSingleton('cron/schedule');

$where_condition_array = array('job_code=?' => 'reverb_sync_orders', 'status=?' => 'pending');
$rows_deleted = $this->getConnection()->delete($cronResourceSingleton->getMainTable(), $where_condition_array);

$installer->endSetup();
