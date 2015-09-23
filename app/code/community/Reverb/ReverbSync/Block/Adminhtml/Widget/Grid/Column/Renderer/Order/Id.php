<?php
/**
 * Author: Sean Dunagan
 * Created: 9/23/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Id
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    const ANCHOR_TAG_TEMPLATE = '<a href="%s">%s</a>';

    public function getHtmlAnchorLinkToViewOrderPageByReverbOrderId($reverb_order_id)
    {
        $magento_entity_id = Mage::getResourceSingleton('reverbSync/order')
                                ->getMagentoOrderEntityIdByReverbOrderNumber($reverb_order_id);

        if (empty($magento_entity_id))
        {
            return $this->escapeHtml($reverb_order_id);
        }

        $escaped_label = $this->escapeHtml($reverb_order_id);
        $view_order_url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $magento_entity_id));
        return sprintf(self::ANCHOR_TAG_TEMPLATE, $view_order_url, $escaped_label);
    }

    public function _getValue(Varien_Object $row)
    {
        $argumentsObject = $row->getArgumentsObject(true);
        $reverb_order_id = '';
        if (isset($argumentsObject->order_number))
        {
            // Order creation rows
            $reverb_order_id = $argumentsObject->order_number;
        }
        elseif (isset($argumentsObject->reverb_order_id))
        {
            // shipment tracking rows
            $reverb_order_id = $argumentsObject->reverb_order_id;
        }
        if (empty($reverb_order_id))
        {
            // This should never happen
            return '';
        }

        return $this->getHtmlAnchorLinkToViewOrderPageByReverbOrderId($reverb_order_id);
    }
}
