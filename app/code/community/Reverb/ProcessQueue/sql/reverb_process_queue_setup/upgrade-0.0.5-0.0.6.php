<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

$installer = $this;

$installer->startSetup();

$task_table_name = $installer->getTable('reverb_process_queue/task');
$unique_task_table_name = $installer->getTable('reverb_process_queue/task_unique');

$index_fields_array = array('last_executed_at');

$task_table_last_executed_at_index_name = $installer->getIdxName('reverb_process_queue/task', $index_fields_array);
$unique_task_table_last_executed_at_index_name = $installer->getIdxName('reverb_process_queue/task_unique', $index_fields_array);

$installer->getConnection()
    ->addIndex($task_table_name, $task_table_last_executed_at_index_name,
        $index_fields_array, Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->getConnection()
    ->addIndex($unique_task_table_name, $unique_task_table_last_executed_at_index_name,
        $index_fields_array, Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->endSetup();

