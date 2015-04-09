<?php

class Reverb_ReverbSync_Model_Mapper_Product
{
    //function to Map the Mgento and Reverb attributes
    public function productMapping($product)
    { try
        {

        $id = $product->getId();
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty = $stock->getQty();
        $weight = $product->getWeight();
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

        } catch (Exception $e) {
            $excp = 'Message: ' . $e -> getMessage();
            Mage::log($excp);
        }
		Mage::log($fieldsArray,1,"field.log");
        return $fieldsArray;
    }



}
