<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

// Update the reverb_categories database table to reflect the new category schema
$reverb_category_table = $installer->getTable('reverbSync/reverb_category');
// Add the uuid column
$installer->getConnection()->dropColumn($reverb_category_table, 'uuid');
$installer->getConnection()->dropColumn($reverb_category_table, 'parent_uuid');
$installer->getConnection()->addColumn($reverb_category_table, 'uuid', 'varchar(40) NOT NULL');
$installer->getConnection()->addColumn($reverb_category_table, 'parent_uuid', 'varchar(40) NULL');
// Remove the Description column
$installer->getConnection()->dropColumn($reverb_category_table, 'description');

// Add the new Reverb-Magento Categories xref table
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
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )->addIndex(
            $installer->getIdxName('reverbSync/magento_reverb_category_xref', array('reverb_category_uuid')),
            array('reverb_category_uuid'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )->addForeignKey(
            $installer->getFkName('reverbSync/magento_reverb_category_xref', 'magento_category_id', 'catalog/category', 'entity_id'),
            'magento_category_id',
            $this->getTable('catalog/category'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Table mapping Magento categories to Reverb categories');

$installer->getConnection()->createTable($magentoReverbCategoryXrefTable);

// Remap the Magento categories to the Reverb categories using the new database table
$remapHelper = Mage::helper('ReverbSync/category_remap');
/* @var $remapHelper Reverb_ReverbSync_Helper_Category_Remap */
$remapHelper->remapReverbCategories();

// Drop the pre-existing Category xref table
$installer->getConnection()->dropTable('reverb_magento_categories');

// Need to delete all categories in the reverb_categories table which do not have a uuid assigned as they are no longer
//      supported
Mage::helper('ReverbSync/category')->removeCategoriesWithoutUuid();

// Attempt to index the uuid field on the reverb category table
$index_creation_was_successful = false;
try
{
    $reverb_category_table = $installer->getTable('reverbSync/reverb_category');

    // Add an index for the uuid field since UUID should have been filled out by a past migration
    $index_fields_array = array('uuid');

    $installer->getConnection()
        ->addIndex(
            $reverb_category_table,
            $installer->getIdxName($reverb_category_table, $index_fields_array),
            $index_fields_array,
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

    $index_creation_was_successful = true;
}
catch(Exception $e)
{
    $error_message = Mage::helper('ReverbSync')
        ->__('An exception occurred while attempting to index the uuid field on the reverb_category table: %s', $e->getMessage());
    Mage::log($error_message, null, 'reverb_category_uuid_to_slug_mapping.log', true);
}

// Add foreign key to the reverb_categories table for the xref table for the uuid field since UUID should have been
//  filled out by a past migration. Only do this if the index creation above was successful
if ($index_creation_was_successful)
{
    try
    {
        $reverb_category_xref_table = $installer->getTable('reverbSync/magento_reverb_category_xref');

        $installer->getConnection()
            ->addForeignKey(
                $installer->getFkName('reverbSync/magento_reverb_category_xref', 'reverb_category_uuid',
                    'reverbSync/reverb_category', 'uuid'),
                $reverb_category_xref_table,
                'reverb_category_uuid',
                $this->getTable('reverbSync/reverb_category'), 'uuid',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            );
    }
    catch(Exception $e)
    {
        $error_message = Mage::helper('ReverbSync')
            ->__('An exception occurred while attempting to add a foreign key constraint to the Reverb Magento Category xref table: %s', $e->getMessage());
        Mage::log($error_message, null, 'reverb_category_uuid_to_slug_mapping.log', true);
    }
}


$installer->endSetup();
