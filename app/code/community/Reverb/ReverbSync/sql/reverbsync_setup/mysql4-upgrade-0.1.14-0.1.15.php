<?php
/*
 * Updating the reverb_categories database table to reflect the new category schema
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$reverb_category_table = $installer->getTable('reverbSync/reverb_category');

// Add the uuid column
$installer->getConnection()->dropColumn($reverb_category_table, 'uuid');

$installer->getConnection()->addColumn($reverb_category_table, 'uuid', 'varchar(40) NOT NULL');

// Remove the Description column

$installer->getConnection()->dropColumn($reverb_category_table, 'description');

$installer->endSetup();
