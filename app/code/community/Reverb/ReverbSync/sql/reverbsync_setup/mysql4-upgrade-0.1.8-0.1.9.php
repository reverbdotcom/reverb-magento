<?php

$installer = $this;
$installer->startSetup();

$magento_reverb_category_tablename = $this->getTable('reverbSync/magento_reverb_category_mapping');

$installer->getConnection()->dropTable($installer->getTable('reverbSync/magento_reverb_category_mapping'));

$magentoReverbCategoryTable =
    $installer->getConnection()
        ->newTable($magento_reverb_category_tablename)
        ->addColumn(
            'xref_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array('nullable'  => false, 'unsigned' => true, 'primary' => true, 'identity'  => true),
            'Primary Key for the Table'
        )->addColumn(
            'magento_category_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array('nullable'  => false),
            'Magento category entity id'
        )->addColumn(
            'reverb_category_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array('nullable'  => false),
            'Id corresponding to a reverb_category_id column value in the reverb_categories table'
        )->addIndex(
            $installer->getIdxName('reverbSync/magento_reverb_category_mapping', array('magento_category_id')),
            array('magento_category_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )->addForeignKey(
            $installer->getFkName('reverbSync/magento_reverb_category_mapping', 'magento_category_id', 'catalog/category', 'entity_id'),
            'magento_category_id',
            $installer->getTable('catalog/category'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('reverbSync/magento_reverb_category_mapping', 'reverb_category_id', 'reverbSync/reverb_category', 'reverb_category_id'),
            'reverb_category_id',
            $installer->getTable('reverbSync/reverb_category'), 'reverb_category_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Table mapping Magento to Reverb Categories');

$installer->getConnection()->createTable($magentoReverbCategoryTable);

$installer->endSetup();
