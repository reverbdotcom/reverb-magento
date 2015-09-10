<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Helper_Orders_Sync extends Mage_Core_Helper_Abstract
{
    const ORDER_SYNC_DISABLED_MESSAGE = 'Order Sync Is Not Enabled in System -> Configuration -> Reverb Configuration -> Order Sync -> Enable Order Sync';
    const ORDER_SYNC_ENABLED_CONFIG_PATH = 'ReverbSync/orders_sync/enabled';

    protected $_moduleName = 'ReverbSync';

    protected $_order_sync_is_disabled_message = null;

    public function isOrderSyncEnabled()
    {
        return Mage::getStoreConfig(self::ORDER_SYNC_ENABLED_CONFIG_PATH);
    }

    public function logOrderSyncDisabledMessage()
    {
        $error_message = $this->getOrderSyncIsDisabledMessage();
        Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
    }

    public function getOrderSyncIsDisabledMessage()
    {
        if (is_null($this->_order_sync_is_disabled_message))
        {
            $this->_order_sync_is_disabled_message = $this->__(self::ORDER_SYNC_DISABLED_MESSAGE);
        }

        return $this->_order_sync_is_disabled_message;
    }
}
