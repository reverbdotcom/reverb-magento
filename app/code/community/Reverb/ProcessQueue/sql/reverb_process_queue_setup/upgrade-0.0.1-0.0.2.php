<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

$installer = $this;

$installer->startSetup();

$process_queue_task_unique_tablename = $this->getTable('reverb_process_queue/task_unique');

$installer->getConnection()->dropTable($installer->getTable('reverb_process_queue/task_unique'));

$processQueueTaskUniqueTable =
    $installer->getConnection()
        ->newTable($process_queue_task_unique_tablename)
        ->addColumn(
            'task_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array('nullable'  => false, 'unsigned' => true, 'primary' => true, 'identity'  => true),
            'Primary Key for the Table'
        )->addColumn(
            'code',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            50,
            array('nullable'  => false),
            'Code representing the process this task is an instance of'
        )->addColumn(
            'unique_id',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            50,
            array('nullable'  => false),
            'Unique Identifier regarding this task'
        )->addColumn(
            'status',
            Varien_Db_Ddl_Table::TYPE_TINYINT,
            3,
            array('nullable'  => false),
            'Status of this task'
        )->addColumn(
            'object',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            100,
            array('nullable'  => false),
            'The object to call the task\'s method on. This can be a magento classname or an actuall object class'
        )->addColumn(
            'method',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            100,
            array('nullable'  => false),
            'The method to call.'
        )->addColumn(
            'serialized_arguments_object',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            array(),
            'A serialized object which will be passed as the only argument to the method'
        )->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array('null' => false, 'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT),
            'The date time this task was entered into the queue'
        )->addColumn(
            'last_executed_at',
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            array('null' => false, 'default' => '0000-00-00 00:00:00'),
            'The date time this task was last attempted to be processed'
        )->addIndex(
            $installer->getIdxName('reverb_process_queue/task_unique', array('code', 'status', 'last_executed_at')),
            array('code', 'status', 'last_executed_at'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->addIndex(
            $installer->getIdxName('reverb_process_queue/task_unique', array('status', 'last_executed_at')),
            array('status', 'last_executed_at'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->addIndex(
            $installer->getIdxName('reverb_process_queue/task_unique', array('code', 'unique_id')),
            array('code', 'unique_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )->setComment('Table abstracting background tasks to be processed via crontab');

$installer->getConnection()->createTable($processQueueTaskUniqueTable);

$installer->endSetup();

