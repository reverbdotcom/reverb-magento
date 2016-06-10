<?php
/**
 * The purpose of this migration is to prevent the Reverb condition value "Like New" from being transmitted to Reverb.
 * "Like New" is a legacy condition that is no longer supported.
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

// Define the legacy condition value we want to update
$legacy_condition_value = 'Like New';
$updated_condition_value = Reverb_ReverbSync_Model_Source_Listing_Condition::B_STOCK;

try
{
    // First update the core_config_data value if it is set to the legacy value we want to update
    $coreConfigAdapter = Mage::getResourceSingleton('reverbSync/core_config');
    /* @var $coreConfigAdapter Reverb_ReverbSync_Model_Mysql4_Core_Config */

    $reverb_condition_config_path = Reverb_ReverbSync_Model_Mapper_Product::LISTING_DEFAULT_CONDITION_CONFIG_PATH;
    $default_reverb_product_condition = $coreConfigAdapter->getConfigValue($reverb_condition_config_path);

    if (!strcmp($default_reverb_product_condition, $legacy_condition_value))
    {
        $coreConfigAdapter->updateConfigValue($reverb_condition_config_path, $updated_condition_value);
    }
}
catch(Exception $e)
{
    $error_message = sprintf('An exception occurred while attempting to ensure the default Reverb condition is properly set: %s', $e->getMessage());
    Mage::log($error_message, null, 'reverb_migrations.log', true);
}

// Update any product attribute values which are set to the legacy value
// First, get the entity_ids for all products which have the reverb_condition value set to "Like New"
$reverb_condition_attribute_code = Reverb_ReverbSync_Model_Mapper_Product::REVERB_CONDITION_PRODUCT_ATTRIBUTE;

try
{
    $likeNewReverbProductsCollection = Mage::getModel('catalog/product')
                                        ->getCollection()
                                        ->addAttributeToFilter($reverb_condition_attribute_code, $legacy_condition_value);

    // Now update the reverb_condition value for all of these products to the new value
    $product_ids = array();
    foreach($likeNewReverbProductsCollection->getItems() as $likeNewReverbProduct)
    {
        $product_ids[] = $likeNewReverbProduct->getId();
    }

    $store_id = Mage_Core_Model_App::ADMIN_STORE_ID;
    $attribute_value_to_update_to = array($reverb_condition_attribute_code => $updated_condition_value);

    Mage::getResourceSingleton('catalog/product_action')->updateAttributes($product_ids, $attribute_value_to_update_to, $store_id);
}
catch(Exception $e)
{
    $error_message = sprintf('An exception occurred while attempting to update Reverb condition product attribute values: %s', $e->getMessage());
    Mage::log($error_message, null, 'reverb_migrations.log', true);
}

$installer->endSetup();
