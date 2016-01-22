<?php

$installer = $this;
$installer->startSetup();

Mage::getResourceSingleton('reverbSync/category_reverb')->initializeReverbCategoriesTable();

$installer->endSetup();
