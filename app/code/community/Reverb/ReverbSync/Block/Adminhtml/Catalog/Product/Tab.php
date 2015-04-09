<?php

class Reverb_ReverbSync_Block_Adminhtml_Catalog_Product_Tab extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    /**
     * Set the template for the block
     *
     */
    public function _construct() {
        parent::_construct();
        $this -> setTemplate('ReverbSync/product/revproduct.phtml');
    }

    //label name for reverb  product info in product edit section
    public function getTabLabel() {
        return $this -> __('Reverb Info');
    }

    public function getTabTitle() {
        return $this -> __('Click here to offline product sync');
    }

    public function canShowTab() {
        if (Mage::getStoreConfig('ReverbSync/extensionOption_group/module_select')) {
            if ($this -> getProductId())
                return true;
            else
                false;
        }
    }

    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden() {
        return false;
    }

    /**
     * create function  getting current product id in template file
     * @return string
     */
    public function getProductId() {
        return Mage::registry('current_product') -> getId();
        //current product id
    }

    /**
     * Returns target product id
     * @return string
     */
    public function getTargetProductId() {
        try {
            $productId = Mage::registry('current_product') -> getId();
            $targetProductId = Mage::getModel('catalog/product') -> load($productId);
            $revProductId = $targetProductId -> getRevProductId();
        } catch (Exception $e) {
            $excp = 'Message: ' . $e -> getMessage();
            Mage::log($excp);
        }
        return $revProductId;
    }
    public function getTargetProductUrl() {
        try {
            $productId = Mage::registry('current_product') -> getId();
            $targetProductId = Mage::getModel('catalog/product') -> load($productId);
            $revProductUrl = $targetProductId -> getRevProductUrl();
        } catch (Exception $e) {
            $excp = 'Message: ' . $e -> getMessage();
            Mage::log($excp);
        }
        return $revProductUrl;
    }
}
