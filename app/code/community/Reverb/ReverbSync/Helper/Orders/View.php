<?php
/**
 * Author: Sean Dunagan
 * Created: 9/18/15
 */

class Reverb_ReverbSync_Helper_Orders_View
{
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
