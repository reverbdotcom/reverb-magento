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

    protected $_image_sync_is_enabled = null;
    protected $_condition = null;
    protected $_has_inventory = null;

    //function to Map the Mgento and Reverb attributes
    public function getUpdateListingWrapper(Mage_Catalog_Model_Product $product)
    {
        $reverbListingWrapper = Mage::getModel('reverbSync/wrapper_listing');
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty = $stock->getQty();
        $hasInventory = $this->_getHasInventory();
        $sku = $product->getSku();

        // We are only syncing quantity on updates. In the future, the rest of these
        // will be put behind switches so that sellers can decide whether they want
        // to sync title and price
        //
        // $price = $product->getPrice();
        // $name = $product->getName();
        // $condition = $this->_getCondition();

        $fieldsArray = array(
                'sku'=> $sku,
                "has_inventory"=>$hasInventory,
                "inventory"=>$qty,
                // 'title'=> $name,
                // 'condition' => $condition,
                // "price"=>$price
               );

        // We only want to bulk add images during the listing creation sync
        //$this->addProductImagesToFieldsArray($fieldsArray, $product);

        $reverbListingWrapper->setApiCallContentData($fieldsArray);
        $reverbListingWrapper->setMagentoProduct($product);

        return $reverbListingWrapper;
    }

    public function getCreateListingWrapper(Mage_Catalog_Model_Product $product)
    {
        $reverbListingWrapper = Mage::getModel('reverbSync/wrapper_listing');
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty = $stock->getQty();
        $price = $product->getPrice();
        $name = $product->getName();
        $description = $product->getDescription();
        $sku = $product->getSku();
        $condition = $this->_getCondition();
        $hasInventory = $this->_getHasInventory();

        $fieldsArray = array(
            'title'=> $name,
            'sku'=> $sku,
            'description'=>$description,
            'condition' => $condition,
            "has_inventory"=>$hasInventory,
            "inventory"=>$qty,
            "price"=>$price
        );

        $this->addProductImagesToFieldsArray($fieldsArray, $product);

        $reverbListingWrapper->setApiCallContentData($fieldsArray);
        $reverbListingWrapper->setMagentoProduct($product);

        return $reverbListingWrapper;
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
