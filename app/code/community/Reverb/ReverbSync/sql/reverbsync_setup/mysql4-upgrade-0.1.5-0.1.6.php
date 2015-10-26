<?php

$installer  = $this;
$installer->startSetup();

$order_item_table_name = $installer->getTable('sales/order_item');

$installer->getConnection()->dropColumn($order_item_table_name, 'reverb_item_link');

$installer->getConnection()->addColumn($order_item_table_name, 'reverb_item_link', 'TEXT DEFAULT NULL');

$quote_item_table_name = $installer->getTable('sales/quote_item');

$installer->getConnection()->dropColumn($quote_item_table_name, 'reverb_item_link');

$installer->getConnection()->addColumn($quote_item_table_name, 'reverb_item_link', 'TEXT DEFAULT NULL');

$installer->endSetup();
