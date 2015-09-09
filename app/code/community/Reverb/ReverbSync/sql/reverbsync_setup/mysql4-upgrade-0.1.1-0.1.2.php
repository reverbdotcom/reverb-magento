<?php

$installer  = $this;
$installer->startSetup();

$table_name = $installer->getTable('sales/order');

$installer->getConnection()->dropColumn($table_name, 'reverb_order_id');

$installer->getConnection()->addColumn($table_name, 'reverb_order_id', 'int(11) unsigned DEFAULT NULL');

$index_fields_array = array('reverb_order_id');

$installer->getConnection()
    ->addIndex(
        $table_name,
        'reverb_order_id',
        $index_fields_array,
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->endSetup();
