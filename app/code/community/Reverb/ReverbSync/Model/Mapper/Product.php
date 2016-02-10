<?php

/**
 * Author: Sean Dunagan
 * Created: 8/17/15
 * Class Reverb_ReverbSync_Model_Mapper_Product
 *
 * This model meant to be referenced as a Singleton via Mage::getSingleton() functionality
 */
class Reverb_ReverbSync_Model_Mapper_Product
{
    const LISTING_CREATION_ENABLED_CONFIG_PATH = 'ReverbSync/reverbDefault/enable_image_sync';
    const LISTING_DEFAULT_CONDITION_CONFIG_PATH = 'ReverbSync/reverbDefault/revCond';
    const REVERB_LISTING_FIELD_PRODUCT_ATTRIBUTE_CONFIG = 'ReverbSync/listings_field_attributes/%s';

    protected $_image_sync_is_enabled = null;
    protected $_condition = null;
    protected $_has_inventory = null;
    protected $_listingsUpdateSyncHelper = null;
    protected $_categorySyncHelper = null;
    protected $_reverbConditionSourceModel = null;
    protected $_reverb_field_product_attributes = array();

    protected $_reverb_fields_mapped_to_magento_attributes = array('make', 'model');

    //LEGACY CODE: function to Map the Magento and Reverb attributes
    public function getUpdateListingWrapper(Mage_Catalog_Model_Product $product)
    {
        $reverbListingWrapper = Mage::getModel('reverbSync/wrapper_listing');
        $sku = $product->getSku();
        // $condition = $this->_getCondition();

        $fieldsArray = array('sku'=> $sku);

        if ($this->_getListingsUpdateSyncHelper()->isTitleUpdateEnabled())
        {
            $fieldsArray['title'] = $product->getName();
        }

        if ($this->_getListingsUpdateSyncHelper()->isPriceUpdateEnabled())
        {
            $fieldsArray['price'] = $this->getProductPrice($product);
        }

        if ($this->_getListingsUpdateSyncHelper()->isInventoryQtyUpdateEnabled())
        {
            $hasInventory = $this->_getHasInventory();
            $fieldsArray['has_inventory'] = $hasInventory;

            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $fieldsArray['inventory'] = $stock->getQty();
        }


        $this->addCategoryToFieldsArray($fieldsArray, $product);
        $this->addProductConditionIfSet($fieldsArray, $product);

        $reverbListingWrapper->setApiCallContentData($fieldsArray);
        $reverbListingWrapper->setMagentoProduct($product);

        return $reverbListingWrapper;
    }

    public function getCreateListingWrapper(Mage_Catalog_Model_Product $product)
    {
        $reverbListingWrapper = Mage::getModel('reverbSync/wrapper_listing');
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty = $stock->getQty();
        $price = $this->getProductPrice($product);
        $name = $product->getName();
        $description = $product->getDescription();
        $sku = $product->getSku();
        $hasInventory = $this->_getHasInventory();

        $fieldsArray = array(
            'title'=> $name,
            'sku'=> $sku,
            'description'=>$description,
            "has_inventory"=>$hasInventory,
            "inventory"=>$qty,
            "price"=>$price
        );

        foreach($this->_reverb_fields_mapped_to_magento_attributes as $reverb_field)
        {
            $product_value = $this->getProductValueForListing($product, $reverb_field);
            if ((!is_null($product_value)) && ($product_value !== ''))
            {
                $fieldsArray[$reverb_field] = $product_value;
            }
        }

        $this->addProductImagesToFieldsArray($fieldsArray, $product);
        $this->addCategoryToFieldsArray($fieldsArray, $product);
        $this->addProductConditionIfSet($fieldsArray, $product);

        $reverbListingWrapper->setApiCallContentData($fieldsArray);
        $reverbListingWrapper->setMagentoProduct($product);

        return $reverbListingWrapper;
    }

    public function getProductPrice($product)
    {
        $attribute_for_reverb_price = $this->getMagentoProductAttributeForReverbField('price');
        if (!empty($attribute_for_reverb_price))
        {
            $reverb_price = $product->getData($attribute_for_reverb_price);
            if (!empty($reverb_price))
            {
                return $reverb_price;
            }
        }
        return $product->getPrice();
    }

    public function getProductValueForListing($product, $reverb_field)
    {
        $product_attribute = $this->getMagentoProductAttributeForReverbField($reverb_field);
        if (!empty($product_attribute))
        {
            return $product->getData($product_attribute);
        }
        return null;
    }

    public function getMagentoProductAttributeForReverbField($reverb_field)
    {
        if (!isset($this->_reverb_field_product_attributes[$reverb_field]))
        {
            $config_value = sprintf(self::REVERB_LISTING_FIELD_PRODUCT_ATTRIBUTE_CONFIG, $reverb_field);
            $this->_reverb_field_product_attributes[$reverb_field] = Mage::getStoreConfig($config_value);
        }

        return $this->_reverb_field_product_attributes[$reverb_field];
    }

    public function addProductConditionIfSet(array &$fieldsArray, $product)
    {
        $_product_condition = $product->getAttributeText('reverb_condition');

        // Get default value if condition is not set
        if (empty($_product_condition))
            $_product_condition = Mage::getStoreConfig(self::LISTING_DEFAULT_CONDITION_CONFIG_PATH);

        if (!empty($_product_condition) && $this->_getReverbConditionSourceModel()->isValidConditionValue($_product_condition))
            $fieldsArray['condition'] = $_product_condition;

        return $fieldsArray;
    }

    public function addCategoryToFieldsArray(array &$fieldsArray, $product)
    {
        $fieldsArray = $this->_getCategorySyncHelper()->addCategoriesToListingFieldsArray($fieldsArray, $product);
        return $fieldsArray;
    }

    public function addProductImagesToFieldsArray(&$fieldsArray, Mage_Catalog_Model_Product $product)
    {
        if (!$this->_getImageSyncIsEnabled())
        {
            return;
        }

        try
        {
            $gallery_image_urls_array = array();
            $galleryImagesCollection = $product->getMediaGalleryImages();
            if (is_object($galleryImagesCollection))
            {
                $gallery_image_items = $galleryImagesCollection->getItems();
                foreach($gallery_image_items as $galleryImageObject)
                {
                    $full_image_url = $galleryImageObject->getUrl();
                    $gallery_image_urls_array[] = $full_image_url;
                }
                // Remove any potential duplicates
                $unique_image_urls_array = array_unique($gallery_image_urls_array);
                $fieldsArray['photos'] = $unique_image_urls_array;
            }
        }
        catch(Exception $e)
        {
            // Do nothing here
        }
    }

    protected function _getReverbConditionSourceModel()
    {
        if (is_null($this->_reverbConditionSourceModel))
        {
            $this->_reverbConditionSourceModel = Mage::getSingleton('reverbSync/source_listing_condition');
        }

        return $this->_reverbConditionSourceModel;
    }

    protected function _getCategorySyncHelper()
    {
        if (is_null($this->_categorySyncHelper))
        {
            $this->_categorySyncHelper = Mage::helper('ReverbSync/sync_category');
        }

        return $this->_categorySyncHelper;
    }

    protected function _getListingsUpdateSyncHelper()
    {
        if (is_null($this->_listingsUpdateSyncHelper))
        {
            $this->_listingsUpdateSyncHelper = Mage::helper('ReverbSync/sync_listings_update');
        }

        return $this->_listingsUpdateSyncHelper;
    }

    protected function _getImageSyncIsEnabled()
    {
        if (is_null($this->_image_sync_is_enabled))
        {
            $this->_image_sync_is_enabled = Mage::getStoreConfig(self::LISTING_CREATION_ENABLED_CONFIG_PATH);
        }

        return $this->_image_sync_is_enabled;
    }

    protected function _getCondition()
    {
        if (is_null($this->_condition))
        {
            $this->_condition = Mage::getStoreConfig('ReverbSync/reverbDefault/revCond');
        }

        return $this->_condition;
    }

    protected function _getHasInventory()
    {
        if (is_null($this->_has_inventory))
        {
            $this->_has_inventory = Mage::getStoreConfig('ReverbSync/reverbDefault/revInvent');
        }

        return $this->_has_inventory;
    }
}
