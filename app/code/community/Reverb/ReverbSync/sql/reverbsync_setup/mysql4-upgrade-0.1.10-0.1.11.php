<?php

$installer = $this;
$installer->startSetup();

/*
    This line has been commented out as this functionality is now deprecated. Population of the categories table
    now takes place in a later migration file in this sequence
*/
//Mage::getResourceSingleton('reverbSync/category_reverb')->initializeReverbCategoriesTable();

$installer->endSetup();
