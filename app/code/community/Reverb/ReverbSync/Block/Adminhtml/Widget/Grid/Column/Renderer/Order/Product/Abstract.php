<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

abstract class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Product_Abstract
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    const ANCHOR_TAG_TEMPLATE = '<a href="%s">%s</a>';

    public function getHtmlAnchorLinkToProductEditPage($label, $magentoProduct)
    {
        $escaped_label = $this->escapeHtml($label);
        $product_edit_url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $magentoProduct->getId()));
        return sprintf(self::ANCHOR_TAG_TEMPLATE, $product_edit_url, $escaped_label);
    }

    protected function _getMagentoProductForRow(Varien_Object $row)
    {
        $magentoProduct = $row->getReverbMagentoProduct();
        if (is_object($magentoProduct) && $magentoProduct->getId())
        {
            return $magentoProduct;
        }

        $argumentsObject = $row->getArgumentsObject(true);
        if (isset($argumentsObject->sku))
        {
            $sku = $this->escapeHtml($argumentsObject->sku);
            $product_id = Mage::getResourceSingleton('catalog/product')->getIdBySku($sku);
            $magentoProduct = Mage::getModel('catalog/product')->load($product_id);
            $row->setReverbMagentoProduct($magentoProduct);
            return $magentoProduct;
        }
        // This case should not happen
        return null;
    }
}
