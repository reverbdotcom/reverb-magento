<?php
/**
 * Author: Sean Dunagan
 * Created: 9/24/15
 */

class Reverb_ReverbSync_Helper_Sync_Image extends Mage_Core_Helper_Data
{
    const ERROR_PROCESSING_GALLERY_IMAGE_LISTING_SYNC = 'An error occurred while attempting to sync gallery image with value_id %s for product with sku %s: %s';

    const LISTINGS_IMAGE_SYNC_ACL_PATH = 'reverb/reverb_listings_image_sync_update';

    protected $_moduleName = 'ReverbSync';
    protected $_imageSyncResourceSingleton = null;

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return int - The number of images queued for sync
     * @throws Reverb_ReverbSync_Model_Exception_Listing_Image_Sync
     */
    public function queueImageSyncForProductGalleryImages(Mage_Catalog_Model_Product $product)
    {
        $images_queued_for_sync = 0;

        try
        {
            $gallery_image_items = $this->_getGalleryImageItemsForProductListing($product);
        }
        catch(Exception $e)
        {
            // Do nothing here under the assumption that this product has no gallery images
            return $images_queued_for_sync;
        }

        if (empty($gallery_image_items))
        {
            return $images_queued_for_sync;
        }

        $sku = $product->getSku();
        $errors_to_throw = array();
        foreach($gallery_image_items as $galleryImageObject)
        {
            try
            {
                $queue_task_rows_inserted = $this->processListingGalleryImageSyncIfNecessary($sku, $galleryImageObject);
                $images_queued_for_sync = $images_queued_for_sync + $queue_task_rows_inserted;
            }
            catch(Exception $e)
            {
                $error_message = $this->__(self::ERROR_PROCESSING_GALLERY_IMAGE_LISTING_SYNC, $galleryImageObject->getValueId(), $sku, $e->getMessage());
                $errors_to_throw[] = $error_message;
                $this->_logError($error_message);
            }
        }

        if (!empty($errors_to_throw))
        {
            $error_message_string = implode('; ',  $errors_to_throw);
            throw new Reverb_ReverbSync_Model_Exception_Listing_Image_Sync($error_message_string);
        }

        return $images_queued_for_sync;
    }

    protected function _getGalleryImageItemsForProductListing(Mage_Catalog_Model_Product $product)
    {
        $gallery_image_items = $this->_getGalleryImageItems($product);

        if (empty($gallery_image_items))
        {
            // See if this is a child product whose parent product has images
            $parentProduct = Mage::helper('reverb_base/product')->getParentProductIfChild($product);
            if ((!is_object($parentProduct)) || (!$parentProduct->getId()))
            {
                return $gallery_image_items;
            }

            $gallery_image_items = $this->_getGalleryImageItems($parentProduct);
        }

        return $gallery_image_items;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getGalleryImageItems(Mage_Catalog_Model_Product $product)
    {
        try
        {
            $galleryImagesCollection = $product->getMediaGalleryImages();
        }
        catch(Exception $e)
        {
            // Do nothing here under the assumption that this product has no gallery images
            return array();
        }

        if (!is_object($galleryImagesCollection) || ($galleryImagesCollection->count() < 1))
        {
            return array();
        }

        return $galleryImagesCollection->getItems();
    }

    public function getSkuForTask(Reverb_ProcessQueue_Model_Task $uniqueQueueTask)
    {
        $unique_id = $uniqueQueueTask->getUniqueId();
        $exploded_unique_id = explode('_', $unique_id);
        array_pop($exploded_unique_id);
        return implode('_', $exploded_unique_id);
    }

    /**
     * @param $sku
     * @param Varien_Object $galleryImageObject
     * @return int - The number of queue task rows inserted into the database
     */
    public function processListingGalleryImageSyncIfNecessary($sku, Varien_Object $galleryImageObject)
    {
        if (!$this->doesGalleryImageQueueTaskExist($sku, $galleryImageObject))
        {
            return $this->_getImageSyncTaskResource()->queueListingImageSync($sku, $galleryImageObject);
        }

        return 0;
    }

    public function doesGalleryImageQueueTaskExist($sku, Varien_Object $galleryImageObject)
    {
        $image_sync_unique_id = $this->getImageSyncUniqueIdValue($sku, $galleryImageObject);
        $unique_task_primary_key = $this->_getImageSyncTaskResource()->getPrimaryKeyByUniqueId($image_sync_unique_id);
        return (!empty($unique_task_primary_key));
    }

    public function getImageSyncUniqueIdValue($sku, Varien_Object $galleryImageObject)
    {
        $catalog_product_entity_media_gallery_id = $galleryImageObject->getValueId();
        $unique_id = $sku . '_' . $catalog_product_entity_media_gallery_id;
        return $unique_id;
    }

    public function canAdminChangeListingsSyncStatus()
    {
        return Mage::getSingleton('admin/session')->isAllowed(self::LISTINGS_IMAGE_SYNC_ACL_PATH);
    }

    protected function _getImageSyncTaskResource()
    {
        if (is_null($this->_imageSyncResourceSingleton))
        {
            $this->_imageSyncResourceSingleton = Mage::getResourceSingleton('reverbSync/task_image_sync');
        }

        return $this->_imageSyncResourceSingleton;
    }

    protected function _logError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logListingImageSyncError($error_message);
    }
} 