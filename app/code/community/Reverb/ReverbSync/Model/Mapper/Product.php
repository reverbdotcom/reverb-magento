<?php

class Reverb_ReverbSync_Model_Mapper_Product
{
    //function to Map the Mgento and Reverb attributes
    public function getListingWrapper($product)
    {
        $reverbListingWrapper = Mage::getModel('reverbSync/wrapper_listing');

        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty = $stock->getQty();
        $price = $product->getPrice();
        $name = $product->getName();
        $description = $product->getDescription();
        $sku = $product->getSku();
        $condition = Mage::getStoreConfig('ReverbSync/reverbDefault/revCond');
        $hasInventory = Mage::getStoreConfig('ReverbSync/reverbDefault/revInvent');

        $fieldsArray = array(
                'title'=> $name,
                'sku'=> $sku,
                'description'=>$description,
                'condition' => $condition,
                "has_inventory"=>$hasInventory,
                "inventory"=>$qty,
                "price"=>$price
               );

        $reverbListingWrapper->setApiCallContentData($fieldsArray);
        $reverbListingWrapper->setMagentoProduct($product);

        return $reverbListingWrapper;
    }



}
