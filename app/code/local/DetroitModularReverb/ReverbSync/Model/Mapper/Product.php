<?php
/**
 * Author: Sean Dunagan (github: dunagan5887)
 * Date: 3/2/18
 */

/**
 * Class DetroitModularReverb_ReverbSync_Model_Mapper_Product
 */
class DetroitModularReverb_ReverbSync_Model_Mapper_Product extends Reverb_ReverbSync_Model_Mapper_Product
{
    const QTY_TO_SET_FOR_INVENTORY = 1;

    /**
     * @param Reverb_ReverbSync_Model_Wrapper_Listing $listingWrapper
     */
    public function setListingQtyToOneIfInventoryIsSet($listingWrapper)
    {
        $api_content_data = $listingWrapper->getApiCallContentData();

        if (isset($api_content_data['inventory']))
        {
            $inventory_level = intval($api_content_data['inventory']);
            if ($inventory_level != 0)
            {
                // Only force the inventory to be 1 if there actually is qty to be set
                $api_content_data['inventory'] = $this->_getQuantityToSetForInventory();

                $listingWrapper->setApiCallContentData($api_content_data);
            }
        }
    }

    /**
     * @return int
     */
    protected function _getQuantityToSetForInventory()
    {
        return self::QTY_TO_SET_FOR_INVENTORY;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function getUpdateListingWrapper(Mage_Catalog_Model_Product $product)
    {
        $listingWrapper = parent::getUpdateListingWrapper($product);
        /* @var Reverb_ReverbSync_Model_Wrapper_Listing $listingWrapper */

        $this->setListingQtyToOneIfInventoryIsSet($listingWrapper);

        return $listingWrapper;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function getCreateListingWrapper(Mage_Catalog_Model_Product $product)
    {
        $listingWrapper =  parent::getCreateListingWrapper($product);
        /* @var Reverb_ReverbSync_Model_Wrapper_Listing $listingWrapper */

        $this->setListingQtyToOneIfInventoryIsSet($listingWrapper);

        return $listingWrapper;
    }
}
