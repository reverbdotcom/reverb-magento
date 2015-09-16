<?php
/**
 * Author: Sean Dunagan
 * Created: 9_16_15
 */

class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Product_Sku
    extends Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Product_Abstract
{
    public function _getValue(Varien_Object $row)
    {
        $magentoProduct = $this->_getMagentoProductForRow($row);
        if ((!is_object($magentoProduct)) || (!$magentoProduct->getId()))
        {
            return null;
        }

        return $this->getHtmlAnchorLinkToProductEditPage($magentoProduct->getSku(), $magentoProduct);
    }
}
