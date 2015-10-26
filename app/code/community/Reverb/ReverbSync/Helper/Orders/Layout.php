<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Helper_Orders_Layout
{
    public function updateAdminOrderViewLayoutForReverbOrder($reverbOrder)
    {
        $reverbOrderTabBlock = Mage::app()->getLayout()->createBlock('ReverbSync/adminhtml_sales_order_view_tab_reverb');
        $reverbOrderTabBlock->setReverbOrder($reverbOrder);
        $salesOrderTabsBlock = Mage::app()->getLayout()->getBlock('sales_order_tabs');
        if (is_object($salesOrderTabsBlock))
        {
            // This would only be false if a third-party extension modified this page's layout
            $salesOrderTabsBlock->addTab('reverb_sync', $reverbOrderTabBlock);
        }

        $orderInfoBlock = Mage::app()->getLayout()->getBlock('order_info');
        $orderInfoBlock->setTemplate('ReverbSync/sales/order/view/info.phtml');
    }

    public function updateAdminOrderViewLayoutForReverbOrderInvoice($reverbOrder)
    {
        $orderInfoBlock = Mage::app()->getLayout()->getBlock('order_info');
        $orderInfoBlock->setTemplate('ReverbSync/sales/order/view/info.phtml');
    }
}
