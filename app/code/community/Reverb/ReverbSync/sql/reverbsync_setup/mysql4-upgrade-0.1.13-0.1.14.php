<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


$reverbInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');


$reverbInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'reverb_offers_enabled', array(
    'label'                 => Mage::helper('ReverbSync')->__('Reverb Accept Offers'),
    'input'                 => 'select',
    'type'                  => 'int',
    'global'                => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'source'                => 'reverbSync/source_listing_offer',
    'visible'               => true,
    'required'              => false,
    'user_defined'          => false,
    'default'               => '0',
    'used_in_product_listing' => false,
    'is_configurable'       => false,
    'visible_on_front'      => false,
    'unique'                => false
));


$installer->endSetup();