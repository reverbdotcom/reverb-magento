<?php
/*
 * Create the new database table mapping Magento category entity ids to Reverb category ids
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$magento_reverb_category_xref_table_name = $this->getTable('reverbSync/magento_reverb_category_xref');

$installer->getConnection()->dropTable($installer->getTable('reverbSync/magento_reverb_category_xref'));

$magentoReverbCategoryXrefTable =
    $installer->getConnection()
        ->newTable($magento_reverb_category_xref_table_name)
        ->addColumn(
            'xref_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array('nullable' => false, 'unsigned' => true, 'primary' => true, 'identity' => true),
            'Primary Key for the Table'
        )->addColumn(
            'reverb_category_uuid',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            40,
            array('nullable' => false),
            'The UUID value uniquely identifying the Reverb Category'
        )->addColumn(
            'magento_category_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            '10',
            array('nullable' => false, 'unsigned' => true),
            'The category entity id in the Magento system mapped to the Reverb category'
        )->addIndex(
            $installer->getIdxName('reverbSync/magento_reverb_category_xref', array('magento_category_id')),
            array('magento_category_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->addIndex(
            $installer->getIdxName('reverbSync/magento_reverb_category_xref', array('xref_id')),
            array('xref_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->addForeignKey(
            $installer->getFkName('reverbSync/magento_reverb_category_xref', 'magento_category_id', 'catalog/category', 'entity_id'),
            'magento_category_id',
            $this->getTable('catalog/category'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Table mapping Magento categories to Reverb categories');

$installer->getConnection()->createTable($magentoReverbCategoryXrefTable);

$installer->endSetup();
