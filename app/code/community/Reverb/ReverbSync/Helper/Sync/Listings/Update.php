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

    protected $_reverb_listing_update_fields = array('sku', 'reverb_sync');

    protected $_mass_product_update_trigger_listings_sync = false;

    /**
     * @param $attributes_data
     * @return bool
     */
    public function shouldMassAttributeUpdateTriggerProductListingsSync($mass_attribute_update_data)
    {
        $magento_attributes = $this->getMassUpdateMagentoAttributesRelevantToReverbListingUpdates();

        $attributes_being_updated = array_keys($mass_attribute_update_data);
        $magento_attributes_being_updated = array_intersect($magento_attributes, $attributes_being_updated);

        if (!empty($magento_attributes_being_updated))
        {
            $this->_mass_product_update_trigger_listings_sync = true;
            return true;
        }

        return false;
    }

    public function shouldMassInventoryUpdateTriggerProductListingsSync()
    {
        if ((!$this->_mass_product_update_trigger_listings_sync) && $this->isInventoryQtyUpdateEnabled())
        {
            $inventory_update_array = Mage::app()->getRequest()->getParam('inventory', array());
            if (isset($inventory_update_array['qty']))
            {
                $this->_mass_product_update_trigger_listings_sync = true;
                return true;
            }
        }

        return false;
    }

    public function getMassUpdateMagentoAttributesRelevantToReverbListingUpdates()
    {
        $magento_attributes = $this->_reverb_listing_update_fields;

        if ($this->isTitleUpdateEnabled())
        {
            $magento_attributes[] = 'name';
        }

        $reverbProductMapper = Mage::getSingleton('reverbSync/mapper_product');
        /* @var $reverbProductMapper Reverb_ReverbSync_Model_Mapper_Product */

        if ($this->isPriceUpdateEnabled())
        {
            $magento_price_attribute = $reverbProductMapper->getMagentoPriceAttributeToMapToReverbPrice();
            $magento_attributes[] = $magento_price_attribute;
        }

        $mapped_magento_attributes = $reverbProductMapper->getMagentoAttributesMappedToReverbAttributes();
        $magento_attributes = array_merge($magento_attributes, $mapped_magento_attributes);

        $reverb_condition_attribute = $reverbProductMapper->getReverbConditionAttribute();
        $magento_attributes[] = $reverb_condition_attribute;

        return $magento_attributes;
    }

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
