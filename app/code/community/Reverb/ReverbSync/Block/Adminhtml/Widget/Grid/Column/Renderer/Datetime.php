<?php
/**
 * Author: Sean Dunagan
 * Created: 9/22/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Widget_Grid_Column_Renderer_Datetime
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
    public function render(Varien_Object $row)
    {
        $data = $this->_getValue($row);
        if (!strcmp('0000-00-00 00:00:00', $data))
        {
            return '';
        }

        return parent::render($row);
    }
}
