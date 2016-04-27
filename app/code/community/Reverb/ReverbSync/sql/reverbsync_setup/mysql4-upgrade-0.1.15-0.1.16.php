<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$magento_reverb_field_mapping_table_name = $this->getTable('reverbSync/magento_reverb_field_mapping');

$installer->getConnection()->dropTable($installer->getTable('reverbSync/magento_reverb_field_mapping'));

$magentoReverbFieldMappingTable =
    $installer->getConnection()
        ->newTable($magento_reverb_field_mapping_table_name)
        ->addColumn(
            'mapping_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array('nullable' => false, 'unsigned' => true, 'primary' => true, 'identity' => true),
            'Primary Key for the Table'
        )->addColumn(
            'magento_attribute_code',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            array('nullable' => false),
            'Attribute code for the Magento attribute'
        )->addColumn(
            'reverb_api_field',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            array('nullable' => false),
            'Reverb Listing API field'
        )->addIndex(
            $installer->getIdxName('reverbSync/magento_reverb_field_mapping', array('magento_attribute_code')),
            array('magento_attribute_code'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->addIndex(
            $installer->getIdxName('reverbSync/magento_reverb_field_mapping', array('reverb_api_field')),
            array('reverb_api_field'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        /*
         * Magento does not have an index in the eav_attribute table where attribute_code is the first column. As such,
         *  we can't create a foreign key on the attribute_code field
         */
        ->setComment('Table abstracting a mapping from Magento attributes to Reverb fields');

$installer->getConnection()->createTable($magentoReverbFieldMappingTable);

$installer->endSetup();
