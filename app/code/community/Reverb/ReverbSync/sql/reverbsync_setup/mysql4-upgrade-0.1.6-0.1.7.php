<?php

$installer = $this;
$installer->startSetup();

$reverb_category_table_name = $this->getTable('reverbSync/reverb_category');

$installer->getConnection()->dropTable($installer->getTable('reverbSync/reverb_category'));

$reverbCategoryTable =
    $installer->getConnection()
        ->newTable($reverb_category_table_name)
        ->addColumn(
            'reverb_category_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array('nullable'  => false, 'unsigned' => true, 'primary' => true, 'identity'  => true),
            'Primary Key for the Table'
        )->addColumn(
            'name',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            array('nullable'  => false),
            'The name of the category'
        )->addColumn(
            'full_name',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            array('nullable'  => false),
            'The full name of the category'
        )->addColumn(
            'reverb_product_type_slug',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            '50',
            array('nullable'  => true),
            'Product Type Slug'
        )->addColumn(
            'reverb_category_slug',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            '50',
            array('nullable'  => true),
            'Category Slug'
        )->addIndex(
            $installer->getIdxName('reverbSync/reverb_category', array('reverb_category_slug')),
            array('reverb_category_slug'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->addIndex(
            $installer->getIdxName('reverbSync/reverb_category', array('reverb_product_type_slug')),
            array('reverb_product_type_slug'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->setComment('Table Holding the Reverb Categories');

$installer->getConnection()->createTable($reverbCategoryTable);
$installer->endSetup();
