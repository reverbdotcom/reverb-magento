<?php  

$installer  = $this;
$installer->startSetup();
$conn = $installer->getConnection();



try
{
    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

    $setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rev_product_url', array(
        'group' => 'General',
        'backend' => '',
        'frontend' => '',
        'label' => 'Rev Product URL',
        'input' => 'hidden',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => 'false',
        'required' => false,
        'user_defined' => false,
        'apply_to' => '',
        'visible_on_front' => false,
        'used_in_product_listing' => false
    ));
}
catch (Exception $excp)
{
    Mage::log($excp->getMessage());  
}
try{
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rev_sync', array(
    'group' => 'General',
     'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => 'Sync to Reverb',
    'input' => 'select',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,    
    'required' => false,
    'default' => '0',
    'user_defined' => false,
    'apply_to' => '',
    'visible_on_front' => false,
    'source' => 'eav/entity_attribute_source_boolean',
    'used_in_product_listing' => false
   
));
}
catch (Exception $excp)
{
    Mage::log($excp->getMessage());  
}
$installer->endSetup();
