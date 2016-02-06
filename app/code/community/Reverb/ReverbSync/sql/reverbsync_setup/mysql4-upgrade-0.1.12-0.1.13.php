<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


$reverbInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');


$reverbInstaller->updateAttribute(Mage_Catalog_Model_Product::ENTITY,
                            'rev_sync',
                            array('attribute_code' => 'reverb_sync',
                                  'frontend_label' => 'Sync to Reverb'));


$reverbInstaller->updateAttribute(Mage_Catalog_Model_Product::ENTITY,
                            'rev_product_url',
                            array('attribute_code' => 'reverb_product_url',
                                  'frontend_label' => 'Reverb Product URL'));

$reverbInstaller->updateAttribute(Mage_Catalog_Model_Product::ENTITY,
                            'rev_product_id',
                            array('attribute_code' => 'reverb_product_id',
                                  'frontend_label' => 'Reverb Product ID'));

$installer->endSetup();