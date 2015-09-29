<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */

class Reverb_Base_Block_Adminhtml_Widget_Grid_Column_Renderer_Product_Edit_Link
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    const ANCHOR_TAG_TEMPLATE = '<a href="%s">%s</a>';

    public function getHtmlAnchorLinkToProductEditPage($label, $magento_product_entity_id)
    {
        $escaped_label = $this->escapeHtml($label);
        $product_edit_url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $magento_product_entity_id));
        return sprintf(self::ANCHOR_TAG_TEMPLATE, $product_edit_url, $escaped_label);
    }
}
