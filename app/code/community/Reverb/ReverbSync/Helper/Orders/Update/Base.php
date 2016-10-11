<?php
/**
 * Author: Sean Dunagan (github: dunagan5887)
 * Date: 10/10/16
 */

/**
 * Class Base
 */
class Reverb_ReverbSync_Helper_Orders_Update_Base extends Reverb_ReverbSync_Helper_Orders_Update_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function getUpdateAction()
    {
        return 'Order Update';
    }
}
