<?php

class Reverb_ReverbSync_Helper_Sync_Product extends Mage_Core_Helper_Data
{
    const UNCAUGHT_EXCEPTION_INDIVIDUAL_PRODUCT_SYNC = 'Error attempting to sync product with id %s with Reverb: %s';
    const PRODUCT_EXCLUDED_FROM_SYNC = 'The "Sync to Reverb" value for this product has been set to "No"; this product can not be synced to Reverb as a result';

    const LISTING_CREATION_ENABLED_CONFIG_PATH = 'ReverbSync/reverbDefault/enable_listing_creation';

    protected $_reverbAdminHelper = null;
    protected $_listing_creation_is_enabled = null;

    public function queueUpBulkProductDataSync()
    {
        // We want this to throw an exception to the calling block if module is not enabled
        $this->_verifyModuleIsEnabled();

        $product_ids_in_system = $this->getReverbSyncEligibleProductIds();

        return Mage::helper('ReverbSync/task_processor')->queueListingsSyncByProductIds($product_ids_in_system);
    }

    public function executeBulkProductDataSync()
    {
        $errors_array = array();

        // We want this to throw an exception to the calling block if module is not enabled
        $this->_verifyModuleIsEnabled();

        $product_ids_in_system = $this->getReverbSyncEligibleProductIds();

        foreach ($product_ids_in_system as $product_id)
        {
            try
            {
                $this->executeIndividualProductDataSync($product_id);
            }
            catch(Exception $e)
            {
                $errors_array[] = $this->__(self::UNCAUGHT_EXCEPTION_INDIVIDUAL_PRODUCT_SYNC, $product_id, $e->getMessage());
            }
        }

        return $errors_array;
    }

    public function getReverbSyncEligibleProductIds()
    {
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('reverb_sync', true);
        $ids = $products->getAllIds();

        return $ids;
    }

    /**
     * Calling block is expected to catch Exceptions. This allows more flexibility in terms of logging any exceptions
     *  as well as redirecting off of them
     *
     * @param $product_id
     * @throws Exception
     */
    public function executeIndividualProductDataSync($product_id, $do_not_allow_creation = false)
    {
        // We want this to throw an exception to the calling block if module is not enabled
        $this->_verifyModuleIsEnabled();
        //load the product
        $product = Mage::getModel('catalog/product')->load($product_id);
        $productType = $product->getTypeID();
        if ($productType != 'simple') {
            throw new Reverb_ReverbSync_Model_Exception_Product_Excluded("Only simple products can be synced.");
        }
        if (!$product->getReverbSync())
        {
            throw new Reverb_ReverbSync_Model_Exception_Product_Excluded(self::PRODUCT_EXCLUDED_FROM_SYNC);
        }

        //pass the data to create or update the product in Reverb
        $listingWrapper = Mage::helper('ReverbSync/data') -> createOrUpdateReverbListing($product, $do_not_allow_creation);
        return $listingWrapper;
    }

    public function deleteAllListingSyncTasks()
    {
        $resourceSingleton = Mage::getResourceSingleton('reverbSync/task_listing');
        return $resourceSingleton->deleteAllListingSyncTasks();
    }

    public function deleteAllReverbReportRows()
    {
        $resourceSingleton = Mage::getResourceSingleton('reverb_reports/reverbreport');
        return $resourceSingleton->deleteAllReverbReportRows();
    }

    public function isListingCreationEnabled()
    {
        if (is_null($this->_listing_creation_is_enabled))
        {
            $listing_creation_enabled = Mage::getStoreConfig(self::LISTING_CREATION_ENABLED_CONFIG_PATH);
            $this->_listing_creation_is_enabled = (!empty($listing_creation_enabled));
        }

        return $this->_listing_creation_is_enabled;
    }

    protected function _verifyModuleIsEnabled()
    {
        return Mage::helper('ReverbSync')->verifyModuleIsEnabled();
    }

    protected function _setAdminSessionErrorMessage($error_message)
    {
        return $this->_getAdminHelper()->addAdminErrorMessage($error_message);
    }

    protected function _getAdminHelper()
    {
        if (is_null($this->_reverbAdminHelper))
        {
            $this->_reverbAdminHelper = Mage::helper('ReverbSync/admin');
        }

        return $this->_reverbAdminHelper;
    }
}
