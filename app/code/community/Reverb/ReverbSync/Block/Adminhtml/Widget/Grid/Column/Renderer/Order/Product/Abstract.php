<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

abstract class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Product_Abstract
    extends Reverb_Base_Block_Adminhtml_Widget_Grid_Column_Renderer_Product_Edit_Link
{
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
            $sku = $argumentsObject->sku;
            return $this->_getAndCacheMagentoProductBySku($sku, $row);
        }
        elseif (isset($argumentsObject->order_id))
        {
            // This could occur with shipment tracking sync rows
            $magento_entity_id = $argumentsObject->order_id;
            if (!empty($magento_entity_id))
            {
                $product_sku_and_name = Mage::getResourceSingleton('reverbSync/order')
                                            ->getOrderItemSkuAndNameByMagentoOrderEntityId($magento_entity_id);
                $sku = isset($product_sku_and_name['sku']) ? $product_sku_and_name['sku'] : null;
                if (!empty($sku))
                {
                    return $this->_getAndCacheMagentoProductBySku($sku, $row);
                }
            }
        }
        // This case should not happen
        return null;
    }

    protected function _getAndCacheMagentoProductBySku($sku, $row)
    {
        $product_id = Mage::getResourceSingleton('catalog/product')->getIdBySku($sku);
        $magentoProduct = Mage::getModel('catalog/product')->load($product_id);
        // Cache the product on the row object
        $row->setReverbMagentoProduct($magentoProduct);
        return $magentoProduct;
    }
}
