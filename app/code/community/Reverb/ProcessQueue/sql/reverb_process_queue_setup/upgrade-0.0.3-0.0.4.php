<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

$installer = $this;

$installer->startSetup();

$task_table_name = $installer->getTable('reverb_process_queue/task');
$unique_task_table_name = $installer->getTable('reverb_process_queue/task_unique');

$installer->getConnection()->addColumn($task_table_name, 'subject_id', 'varchar(100) NULL');
$installer->getConnection()->addColumn($unique_task_table_name, 'subject_id', 'varchar(100) NULL');

$index_fields_array = array('subject_id');

$task_table_subject_id_index_name = $installer->getIdxName('reverb_process_queue/task', array('subject_id'));
$unique_task_table_subject_id_index_name = $installer->getIdxName('reverb_process_queue/task_unique', array('subject_id'));

$installer->getConnection()
    ->addIndex($task_table_name, $task_table_subject_id_index_name,
                $index_fields_array, Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->getConnection()
    ->addIndex($unique_task_table_name, $unique_task_table_subject_id_index_name,
                $index_fields_array, Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->endSetup();

