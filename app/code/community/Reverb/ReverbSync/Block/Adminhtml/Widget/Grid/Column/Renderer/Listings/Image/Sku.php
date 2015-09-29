<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Listings_Image_Sku
    extends Reverb_Base_Block_Adminhtml_Widget_Grid_Column_Renderer_Product_Edit_Link
{
    protected $_imageSyncHelper = null;
    protected $_productResourceSingleton = null;

    protected function _getValue(Varien_Object $row)
    {
        $sku = $this->_getImageSyncHelper()->getSkuForTask($row);
        $id = $this->_getProductResourceSingleton()->getIdBySku($sku);
        return $this->getHtmlAnchorLinkToProductEditPage($sku, $id);
    }

    protected function _getProductResourceSingleton()
    {
        if (is_null($this->_productResourceSingleton))
        {
            $this->_productResourceSingleton = Mage::getResourceSingleton('catalog/product');
        }

        return $this->_productResourceSingleton;
    }

    protected function _getImageSyncHelper()
    {
        if (is_null($this->_imageSyncHelper))
        {
            $this->_imageSyncHelper = Mage::helper('ReverbSync/sync_image');
        }

        return $this->_imageSyncHelper;
    }
} 