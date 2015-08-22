<?php

$installer  = $this;
$installer->startSetup();
$conn = $installer->getConnection();

try
{
    $setup = Mage::getModel('eav/entity_setup', 'core_setup');
    $setup->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'rev_sync', 'default_value', 1);
}
catch (Exception $excp)
{
    Mage::log($excp->getMessage());
}
$installer->endSetup();
