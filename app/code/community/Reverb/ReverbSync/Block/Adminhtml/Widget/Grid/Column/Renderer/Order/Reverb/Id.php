<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Reverb_Id
    extends Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Order_Id
{
    public function _getValue(Varien_Object $row)
    {
        $argumentsObject = $row->getArgumentsObject(true);
        if (isset($argumentsObject->order_number))
        {
            $reverb_order_number = $argumentsObject->order_number;
            return $this->getHtmlAnchorLinkToViewOrderPageByReverbOrderId($reverb_order_number);
        }
        // This should never happen
        return '';
    }
}
