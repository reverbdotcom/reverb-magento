<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

$installer = $this;

$installer->startSetup();

$column_name = 'serialized_arguments_object';

$task_table_name = $installer->getTable('reverb_process_queue/task');
$unique_task_table_name = $installer->getTable('reverb_process_queue/task_unique');

$installer->getConnection()->changeColumn($task_table_name, $column_name, $column_name, 'BLOB NULL');
$installer->getConnection()->changeColumn($unique_task_table_name, $column_name, $column_name, 'BLOB NULL');

$installer->endSetup();

