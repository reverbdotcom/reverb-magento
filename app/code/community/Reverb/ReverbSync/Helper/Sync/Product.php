<?php

class Reverb_ReverbSync_Helper_Sync_Product extends Mage_Core_Helper_Data
{
    const MODULE_NOT_ENABLED = 'The Reverb Module is not enabled. Please enable this functionality in System -> Configuration -> Reverb Configuration -> Reverb Extension';
    const UNCAUGHT_EXCEPTION_INDIVIDUAL_PRODUCT_SYNC = 'An uncaught exception occurred while attempting to sync product with id %s with Reverb: %s';

    protected $_reverbAdminHelper = null;

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

    /**
     * For now, use an adapter query to get all product ids in system: more efficient than querying via
     *  the ORM.
     *
     * For now all products should be considered eligible to be synced with Reverb
     */
    public function getReverbSyncEligibleProductIds()
    {
        $productResourceSingleton = Mage::getSingleton('reverbSync/catalog_resource_product');
        return $productResourceSingleton->getAllProductIdsArray();
    }

    /**
     * Calling block is expected to catch Exceptions. This allows more flexibility in terms of logging any exceptions
     *  as well as redirecting off of them
     *
     * @param $product_id
     * @throws Exception
     */
    public function executeIndividualProductDataSync($product_id)
    {
        // We want this to throw an exception to the calling block if module is not enabled
        $this->_verifyModuleIsEnabled();
        //load the product
        $product = Mage::getModel('catalog/product') -> load($product_id);
        $stock = Mage::getModel('cataloginventory/stock_item') -> loadByProduct($product);
        $productType = $product -> getTypeID();
        if ($productType != 'simple') {
            throw new Exception("Only simple products can be synced.");
        }
        if ($this->_productIsExcludedFromSync($product))
        {
            throw new Exception("This product has been excluded from bring synced");
        }

        $mapperModel = Mage::getModel('reverbSync/Mapper_Product');
        //map the product
        $fieldsArray = $mapperModel -> productMapping($product);
        //pass the data to create or update the product in Reverb
        $responseData = Mage::helper('ReverbSync/data') -> createOrUpdateReverbListing($fieldsArray);
        Mage::helper('ReverbSync/data') -> reverbReports($product_id, $product -> getName(), $product -> getSku(), $stock -> getQty(), $responseData, 1, null);
    }

    protected function _productIsExcludedFromSync($product)
    {
        $revSync = $product -> getRevSync();
        if (is_null($revSync))
        {
            // Default functionality should be to sync product
            return false;
        }

        return ($revSync === 0);
    }

    protected function _verifyModuleIsEnabled()
    {
        $isEnabled = Mage::getStoreConfig('ReverbSync/extensionOption_group/module_select');
        if (!$isEnabled)
        {
            throw new Exception(self::MODULE_NOT_ENABLED);
        }

        return true;
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