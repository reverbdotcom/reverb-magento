<?php

$installer  = $this;
$installer->startSetup();

$table_name = $installer->getTable('sales/order');

$installer->getConnection()->dropColumn($table_name, 'reverb_order_status');

$installer->getConnection()->addColumn($table_name, 'reverb_order_status', 'varchar(25) DEFAULT NULL');

// For now there will not be an index on the column until one is needed for functional specs

$installer->endSetup();
