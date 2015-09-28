<?php
/**
 * Author: Sean Dunagan
 * Created: 9/28/15
 */

class Reverb_ReverbSync_Helper_Sync_Listings_Update
{
    const UPDATE_FIELD_SWITCH_TITLE = 'ReverbSync/listings_update_switches/title';
    const UPDATE_FIELD_SWITCH_PRICE = 'ReverbSync/listings_update_switches/price';
    const UPDATE_FIELD_SWITCH_INVENTORY_QTY = 'ReverbSync/listings_update_switches/inventory_qty';

    public function isTitleUpdateEnabled()
    {
        return Mage::getStoreConfig(self::UPDATE_FIELD_SWITCH_TITLE);
    }

    public function isPriceUpdateEnabled()
    {
        return Mage::getStoreConfig(self::UPDATE_FIELD_SWITCH_PRICE);
    }

    public function isInventoryQtyUpdateEnabled()
    {
        return Mage::getStoreConfig(self::UPDATE_FIELD_SWITCH_INVENTORY_QTY);
    }
}
