<?php

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;

$installer->startSetup();

// Rename attributes to use "reverb_" prefix
// TODO: Attributes could use friendlier labels - e.g. "Reverb.com Product Id" instead of "Rev Product id"
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'rev_sync', array('attribute_code' => 'reverb_sync_enabled'));
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'rev_product_id', array('attribute_code' => 'reverb_product_id'));
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'rev_product_url', array('attribute_code' => 'reverb_product_Url'));

$installer->endSetup();

