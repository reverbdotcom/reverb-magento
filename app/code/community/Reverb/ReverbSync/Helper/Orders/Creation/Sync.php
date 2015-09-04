<?php
/**
 * Author: Sean Dunagan
 * Created: 9/4/15
 */

/**
 * Author: Sean Dunagan
 * Created: 9/4/15
 *
 * Class Reverb_ReverbSync_Helper_Orders_Creation_Sync
 *
 *
 * It was intentional that both Reverb_ReverbSync_Helper_Orders_Creation_Shipping and
 *  Reverb_ReverbSync_Helper_Orders_Creation_Payment both end up setting the order in the same registry key, as they
 *  should be operating on the same order object
 */
class Reverb_ReverbSync_Helper_Orders_Creation_Sync extends Mage_Core_Helper_Abstract
{
    const ORDER_BEING_SYNCED_REGISTRY_KEY = 'current_reverb_sync_order';

    protected $_moduleName = 'ReverbSync';

    protected function _setOrderBeingSyncedInRegistry($reverbOrderObject)
    {
        $this->unsetOrderBeingSynced();
        Mage::register(self::ORDER_BEING_SYNCED_REGISTRY_KEY, $reverbOrderObject);
    }

    public function getOrderBeingSyncedInRegistry()
    {
        return Mage::registry(self::ORDER_BEING_SYNCED_REGISTRY_KEY);
    }

    public function unsetOrderBeingSynced()
    {
        Mage::unregister(self::ORDER_BEING_SYNCED_REGISTRY_KEY);
    }
} 