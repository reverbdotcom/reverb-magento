<?php

class Reverb_ReverbSync_Helper_Sync_Product extends Mage_Core_Helper_Data
{
    const UNCAUGHT_EXCEPTION_INDIVIDUAL_PRODUCT_SYNC = 'Error attempting to sync product with id %s with Reverb: %s';
    const PRODUCT_EXCLUDED_FROM_SYNC = 'The "Sync to Reverb" value for this product has been set to "No"; this product can not be synced to Reverb as a result';
    const ERROR_INVALID_PRODUCT_TYPE = "Only %s products can be synced.";
    const ERROR_INVALID_PRODUCT = 'An attempt was made to sync an unloaded product to Reverb';
    const ERROR_NOT_SIMPLE_PRODUCT = 'Product with sku %s is not a simple product';

    const LISTING_CREATION_ENABLED_CONFIG_PATH = 'ReverbSync/reverbDefault/enable_listing_creation';

    protected $_allowed_product_types_for_sync = array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                                                        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

    protected $_reverbAdminHelper = null;
    protected $_productHelper = null;
    protected $_listing_creation_is_enabled = null;

    public function queueUpBulkProductDataSync()
    {
        // We want this to throw an exception to the calling block if module is not enabled
        $this->_verifyModuleIsEnabled();

        $product_ids_in_system = $this->getReverbSyncEligibleProductIds();

        return Mage::helper('ReverbSync/task_processor')->queueListingsSyncByProductIds($product_ids_in_system);
    }

    public function queueUpProductDataSync(array $product_ids_to_queue)
    {
        $this->_verifyModuleIsEnabled();

        return Mage::helper('ReverbSync/task_processor')->queueListingsSyncByProductIds($product_ids_to_queue);
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
        $products = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addFieldToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                        ->addFieldToFilter('reverb_sync', true);
        $ids = $products->getAllIds();

        return $ids;
    }

    /**
     * Calling block is expected to catch Exceptions. This allows more flexibility in terms of logging any exceptions
     *  as well as redirecting off of them
     *
     * @param $product_id
     * @return array - Array of Reverb_ReverbSync_Model_Wrapper_Listing
     * @throws Exception
     */
    public function executeIndividualProductDataSync($product_id, $do_not_allow_creation = false)
    {
        // We want this to throw an exception to the calling block if module is not enabled
        $this->_verifyModuleIsEnabled();
        //load the product
        $product = Mage::getModel('catalog/product')->load($product_id);
        $productType = $product->getTypeID();
        if (!in_array($productType, $this->_allowed_product_types_for_sync))
        {
            $allowed_product_types = implode(', ', $this->_allowed_product_types_for_sync);
            $error_message = sprintf(self::ERROR_INVALID_PRODUCT_TYPE, $allowed_product_types);
            throw new Reverb_ReverbSync_Model_Exception_Product_Excluded($error_message);
        }
        if (!$product->getReverbSync())
        {
            throw new Reverb_ReverbSync_Model_Exception_Product_Excluded(self::PRODUCT_EXCLUDED_FROM_SYNC);
        }

        $listings_wrapper_array = array();
        if ($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            $listings_wrapper_array[] = $this->executeSimpleProductSync($product, $do_not_allow_creation);
        }
        else
        {
            $child_products = $this->_getProductHelper()->getSimpleProductsForConfigurableProduct($product);
            foreach($child_products as $simpleChildProduct)
            {
                $listings_wrapper_array[] = $this->executeSimpleProductSync($simpleChildProduct, $do_not_allow_creation);
            }
        }

        return $listings_wrapper_array;
    }

    /**
     * @param $simpleProduct
     * @param bool $do_not_allow_creation
     * @return Reverb_ReverbSync_Model_Wrapper_Listing
     * @throws Reverb_ReverbSync_Model_Exception_Product_Excluded
     * @throws Reverb_ReverbSync_Model_Exception_Product_Validation
     */
    public function executeSimpleProductSync($simpleProduct, $do_not_allow_creation = false)
    {
        if ((!is_object($simpleProduct)) || (!$simpleProduct->getId()))
        {
            $error_message = $this->__(self::ERROR_INVALID_PRODUCT);
            throw new Reverb_ReverbSync_Model_Exception_Product_Validation($error_message);
        }

        $productType = $simpleProduct->getTypeId();
        if ($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            //pass the data to create or update the product in Reverb
            $listingWrapper = Mage::helper('ReverbSync/data')
                                ->createOrUpdateReverbListing($simpleProduct, $do_not_allow_creation);
            return $listingWrapper;
        }

        $product_sku = $simpleProduct->getSku();
        $error_message = $this->__(self::ERROR_NOT_SIMPLE_PRODUCT, $product_sku);
        throw new Reverb_ReverbSync_Model_Exception_Product_Excluded($error_message);
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

    /**
     * @return Reverb_Base_Helper_Product
     */
    protected function _getProductHelper()
    {
        if (is_null($this->_productHelper))
        {
            $this->_productHelper = Mage::helper('reverb_base/product');
        }

        return $this->_productHelper;
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
