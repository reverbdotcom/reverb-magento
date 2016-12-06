<?php
/**
 * The purpose of this migration is to remove all COMPLETE/ABORTED tasks of type `order_creation`. Orders will
 *  now be created via the order update process. We don't want to remove any `order_creation` tasks which are in state
 *  PROCESSING as those tasks are currently being executed. Tasks in states PENDING or ERROR may have been selected by
 *  a cron process and may be queued to be processed, so we won't want to interrupt those tasks either.
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

// Populate the Reverb Categories table using the Reverb API
$categoryUpdateSyncHelper = Mage::helper('ReverbSync/sync_category_update');
/* @var $categoryUpdateSyncHelper Reverb_ReverbSync_Helper_Sync_Category_Update */
$categoryUpdateSyncHelper->updateReverbCategoriesFromApi();

$installer->endSetup();
