<?php

class Reverb_ReverbSync_Model_Observer {

    //function to create the product in reverb
    public function productSave($observer) {
        try {
            //check for module enable/disable
            if (!Mage::getStoreConfig('ReverbSync/extensionOption_group/module_select')) {
                return;
            }
            //check for token key
            $revConnection = Mage::helper('ReverbSync/connection') -> revConnection();
            if (!$revConnection)
                return;
            $productId = $observer -> getProduct() -> getId();
            $product = Mage::getModel('catalog/product') -> load($productId);
            $productType = $product -> getTypeID();
            $revSync = $product -> getRevSync();
            //sync only simple and sync to rev is allowed
            if ($productType != 'simple' || !$revSync) {
                return;
            }
            // Map the attributes
            $mapperModel = Mage::getModel('reverbSync/Mapper_Product');
            $fieldsArray = $mapperModel -> productMapping($product);
            $revProductId = $product -> getRevProductId();
            $postData = Mage::app() -> getRequest() -> getParam('revProduct');
            if ($postData != 'saveResponse') {
                //check if the sync product
                if (!isset($revProductId)) {
                    //pass the data to create the product in Reverb
                    $responseData = Mage::helper('ReverbSync/data') -> createObject($revConnection, $fieldsArray, 'listings');
                    Mage::app() -> getRequest() -> setParam('revProduct', "saveResponse");
                    if (isset($responseId)) {
                        $revPurl = parse_url($responseData, PHP_URL_PATH);
                        $revPid = explode("/", $revPurl);
                        $revPid = explode('-', $revPid[2]);
                        $revPid = $revPid[0];
                        $product -> setRevProductId($revPid);
                        $product -> setRevProductUrl($responseData);
                        $product -> save();
                    }
                } else {

                    $responseId = Mage::helper('ReverbSync/data') -> updateObject($revConnection, $fieldsArray, 'listings', $revProductId);
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session') -> addWarning($e -> getMessage());
            
        }

    }

    // funtion to get the product quantity placed through order
    public function orderSave($observer) {

        try {
            #check module enable or disable
            if (!Mage::getStoreConfig('ReverbSync/extensionOption_group/module_select')) {
                return;
            }
            #Get the token key
            $revConnection = Mage::helper('ReverbSync/connection') -> revConnection();
            if (!$revConnection)
                return;
            $order = $observer -> getEvent() -> getOrder();
            $postData = Mage::app() -> getRequest() -> getParam('revOrder');
            if ($postData != 'saveResponse') {
                //Get each product placed in order
                foreach ($order->getAllItems() as $item) {
                    $product = Mage::getModel('catalog/product') -> load($item -> getProductId());
                    $revProductId = $product -> getRevProductId();
                    $revSync = $product -> getRevSync();
                    $stock = Mage::getModel('cataloginventory/stock_item') -> loadByProduct($item -> getProductId());
                    $prodQty = $stock -> getQty();
                    //check for sync and sync to reverb is true or not
                    if ($revProductId && $revSync) {
                        $fieldsArray = array("inventory" => $prodQty);
                        Mage::app() -> getRequest() -> setParam('revOrder', "saveResponse");
                        //pass the data to update the product in reverb
                        $responseId = Mage::helper('ReverbSync/data') -> updateObject($revConnection, $fieldsArray, 'listings', $revProductId);
                    }
                }

            }
        } catch (Exception $e) {
            $excp = 'Message: ' . $e -> getMessage();
            Mage::log($excp);
        }
        return;

    }

}
