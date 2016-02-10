<?php

$installer = $this;
$installer->startSetup();

$eavSetup = Mage::getModel('catalog/resource_eav_mysql4_setup', 'reverbsync_setup');

$entity_type_id = 'catalog_product';
$attribute_code = 'reverb_condition';

$eavSetup->removeAttribute($entity_type_id, $attribute_code);

$eavSetup->addAttribute($entity_type_id, $attribute_code, array(
    'type' => 'varchar',
    'input' => 'select',
    'label' => 'Reverb Condition',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source' => 'reverbSync/source_listing_condition',
    'visible' => 1,
    'visible_on_front' => 0,
    'required' => 0,
    'used_in_product_listing' => 0,
    'is_configurable' => 0,
    'user_defined' => 1,
    'unique' => false,
    'filterable' => 0,
    'filterable_in_search' => 0,
    'group' => 'General'
));

$installer->endSetup();
