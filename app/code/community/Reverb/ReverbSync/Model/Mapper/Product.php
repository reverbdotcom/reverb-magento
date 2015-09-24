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
    protected $_condition = null;
    protected $_has_inventory = null;

    //function to Map the Mgento and Reverb attributes
    public function getUpdateListingWrapper(Mage_Catalog_Model_Product $product)
    {
        $reverbListingWrapper = Mage::getModel('reverbSync/wrapper_listing');
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty = $stock->getQty();
        $price = $product->getPrice();
        $name = $product->getName();
        $sku = $product->getSku();
        $condition = $this->_getCondition();
        $hasInventory = $this->_getHasInventory();

        $fieldsArray = array(
                'title'=> $name,
                'sku'=> $sku,
                'condition' => $condition,
                "has_inventory"=>$hasInventory,
                "inventory"=>$qty,
                "price"=>$price
               );

        $fieldsArray = $this->addBaseImageUrlToArrayIfExists($fieldsArray, $product);

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

        $fieldsArray = $this->addBaseImageUrlToArrayIfExists($fieldsArray, $product);

        $reverbListingWrapper->setApiCallContentData($fieldsArray);
        $reverbListingWrapper->setMagentoProduct($product);

        return $reverbListingWrapper;
    }

    public function addBaseImageUrlToArrayIfExists($fieldsArray, Mage_Catalog_Model_Product $product)
    {
        try
        {
            Mage::getResourceSingleton('catalog/product')->load($product, $product->getId(), array('image'));

            $base_image_path_relative_path = $product->getImage();
            if ((!empty($base_image_path_relative_path)) && (strcmp('no_selection', $base_image_path_relative_path)))
            {
                $base_image_url = Mage::getModel('catalog/product_media_config')
                                    ->getMediaUrl($base_image_path_relative_path);
                $fieldsArray['photos'] = array($base_image_url);
            }
        }
        catch(Exception $e)
        {
            // Do nothing here
        }

        return $fieldsArray;
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
