<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

$installer = $this;

$installer->startSetup();

$task_table_name = $installer->getTable('reverb_process_queue/task');
$unique_task_table_name = $installer->getTable('reverb_process_queue/task_unique');

$installer->getConnection()->addColumn($task_table_name, 'status_message', 'TEXT NULL');
$installer->getConnection()->addColumn($unique_task_table_name, 'status_message', 'TEXT NULL');

$installer->endSetup();

