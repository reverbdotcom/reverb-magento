<?php
/**
 * Author: Sean Dunagan
 * Created: 9/18/15
 */

class Reverb_ReverbSync_Helper_Orders_View
{
    public function getLinkToItemOnReverb($reverbOrder)
    {
        $orderItem = $this->_getReverbOrderItemByOrder($reverbOrder);

        $reverb_item_link = $orderItem->getReverbItemLink();
        if (!empty($reverb_item_link))
        {
            $reverb_base_url = Mage::helper('ReverbSync')->getReverbBaseUrl();
            $item_link = $reverb_base_url . $reverb_item_link;
            return $item_link;
        }

        return '';
    }

    public function getReverbOrderItemSku($reverbOrder)
    {
        $orderItem = $this->_getReverbOrderItemByOrder($reverbOrder);
        if (is_object($orderItem) && $orderItem->getId())
        {
            return $orderItem->getSku();
        }

        return false;
    }

    protected function _getReverbOrderItemByOrder($reverbOrder)
    {
        if ((!is_object($reverbOrder)) || (!$reverbOrder->getId()))
        {
            return false;
        }

        $order_items = $reverbOrder->getAllVisibleItems();
        // There should only be one item in any Reverb Order
        $orderItem = reset($order_items);
        if ((!is_object($orderItem)) || (!$orderItem->getId()))
        {
            return false;
        }

        return $orderItem;
    }

    public function getStoreNameForReverbOrder($reverbOrder)
    {
        if (is_object($reverbOrder) && $reverbOrder->getId())
        {
            $store_name = $reverbOrder->getStoreName();
            if (!empty($store_name))
            {
                return $store_name;
            }
        }
        // The store name should always be set, but handle the case where it isn't
        $orderInfoBlock = Mage::app()->getLayout()->getBlock('order_info');
        return $orderInfoBlock->getOrderStoreName();
    }

    public function getReverbOrderId($reverbOrder)
    {
        if (is_object($reverbOrder) && $reverbOrder->getId())
        {
            return $reverbOrder->getReverbOrderId();
        }
        // This should never happen, but handle the case where it does
        return '';
    }

    public function getReverbOrderStatus($reverbOrder)
    {
        if (is_object($reverbOrder) && $reverbOrder->getId())
        {
            return $reverbOrder->getReverbOrderStatus();
        }
        // This should never happen, but handle the case where it does
        return '';
    }
}
