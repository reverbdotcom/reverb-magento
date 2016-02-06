<?php
/**
 * Reverb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Reverb
 * @package    ReverbSync
 */
?>


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