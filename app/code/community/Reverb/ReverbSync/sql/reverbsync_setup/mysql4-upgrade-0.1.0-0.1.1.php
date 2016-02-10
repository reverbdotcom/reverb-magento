<?php

$installer  = $this;
$installer->startSetup();

try
{
    $reverbInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
    $reverbInstaller->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'rev_sync', 'default_value', 1);
}
catch (Exception $excp)
{
    Mage::log($excp->getMessage());
}
$installer->endSetup();
