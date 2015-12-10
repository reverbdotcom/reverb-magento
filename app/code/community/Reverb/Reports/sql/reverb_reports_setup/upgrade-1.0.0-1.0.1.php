<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 */

$installer = $this;

$installer->startSetup();

$report_table_name = $installer->getTable('reverb_reports/reverbreport');

$index_fields_array = array('last_synced');

$reports_table_last_synced_index_name = $installer->getIdxName('reverb_process_queue/task', $index_fields_array);

$installer->getConnection()
    ->addIndex($report_table_name, $reports_table_last_synced_index_name,
        $index_fields_array, Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->endSetup();

