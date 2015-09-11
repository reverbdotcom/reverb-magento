<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Sales_Order_View_Tab_Reverb
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_reverbOrder = null;

    public function _construct()
    {
        $this->setTemplate('ReverbSync/sales/order/view/tab/info.phtml');
    }

    public function getOrderId()
    {
        $reverbOrder = $this->getReverbOrder();
        if (is_object($reverbOrder) && $reverbOrder->getId())
        {
            return $reverbOrder->getId();
        }

        return null;
    }

    public function getOrderStatus()
    {
        $reverbOrder = $this->getReverbOrder();
        if (is_object($reverbOrder) && $reverbOrder->getId())
        {
            return $reverbOrder->getStatus();
        }

        return null;
    }

    public function getReverbOrder()
    {
        return $this->_reverbOrder;
    }

    public function setReverbOrder($reverbOrder)
    {
        $this->_reverbOrder = $reverbOrder;
        return $this;
    }

    public function getTabLabel()
    {
        return Mage::helper('ReverbSync')->__('Reverb Sync');
    }

    public function getTabTitle()
    {
        return Mage::helper('ReverbSync')->__('Reverb Sync');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
