<?php

/**
 * Reports module install script
 * @category    Reverb
 * @package     Reverb_Reports
 */
$this->startSetup();
$table = $this->getConnection()
    ->newTable($this->getTable('reverb_reports/reverbreport'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Reverb Report ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Product Id')

    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Product Name')

    ->addColumn('product_sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Product Sku')
    ->addColumn('inventory', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Inventory')
        
    ->addColumn('rev_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'REV URL')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Reverb Report Status')
    ->addColumn('sync_details', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sync Details')

    ->addColumn('last_synced', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            ), 'Reverb Report Modification Time')
    
    ->setComment('Reverb Report Table');
$this->getConnection()->createTable($table);
$this->endSetup();
