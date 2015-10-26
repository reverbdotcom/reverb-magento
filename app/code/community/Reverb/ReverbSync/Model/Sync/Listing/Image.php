<?php
/**
 * Author: Sean Dunagan
 * Created: 9/24/15
 */

class Reverb_ReverbSync_Model_Sync_Listing_Image extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_INVALID_SKU = 'An attempt was made to transmit an image to Reverb for an invalid sku: %s';
    const ERROR_EMPTY_IMAGE_URL = 'No image url was set on the Reverb Task Arguments Object';

    /**
     * We expect the calling block to catch exceptions as part of the task processing
     *
     * @param stdClass $argumentsObject
     */
    public function transmitGalleryImageToReverb(stdClass $argumentsObject)
    {
        $sku = isset($argumentsObject->sku) ? $argumentsObject->sku : null;
        // Validate the sku
        $magento_entity_id = Mage::getResourceSingleton('catalog/product')->getIdBySku($sku);
        if (empty($magento_entity_id))
        {
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_INVALID_SKU, $sku);
            return $this->_returnAbortCallbackResult($error_message);
        }

        $image_url = isset($argumentsObject->url) ? $argumentsObject->url : null;
        if (empty($image_url))
        {
            $error_message = Mage::helper('ReverbSync')->__(self::ERROR_EMPTY_IMAGE_URL);
            return $this->_returnAbortCallbackResult($error_message);
        }

        Mage::helper('ReverbSync/api_adapter_listings_image')->transmitGalleryImageToReverb($sku, $image_url);
    }
}
