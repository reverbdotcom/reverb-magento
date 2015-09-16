<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Reverb_Id
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function _getValue(Varien_Object $row)
    {
        $argumentsObject = $row->getArgumentsObject(true);
        if (isset($argumentsObject->order_number))
        {
            $reverb_order_number = $argumentsObject->order_number;
            return $this->escapeHtml($reverb_order_number);
        }
        // This should never happen
        return '';
    }
}
