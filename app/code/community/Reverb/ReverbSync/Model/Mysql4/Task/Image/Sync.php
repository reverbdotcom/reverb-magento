<?php
/**
 * Author: Sean Dunagan
 * Created: 9/24/15
 */

class Reverb_ReverbSync_Model_Mysql4_Task_Image_Sync extends Reverb_ReverbSync_Model_Mysql4_Task_Unique
{
    const ORDER_CREATION_OBJECT = 'reverbSync/sync_listing_image';
    const ORDER_CREATION_METHOD = 'transmitGalleryImageToReverb';

    protected $_task_code = 'listing_image_sync';
    protected $_imageSyncHelper = null;

    public function queueListingImageSync($sku, Varien_Object $galleryImageObject)
    {
        $unique_id_key = $this->_getImageSyncHelper()->getImageSyncUniqueIdValue($sku, $galleryImageObject);

        $relative_image_file_path = $galleryImageObject->getFile();
        $exploded_file_path = explode('/', $relative_image_file_path);
        $image_file_name = end($exploded_file_path);

        $insert_data_array = $this->_getUniqueInsertDataArrayTemplate(self::ORDER_CREATION_OBJECT, self::ORDER_CREATION_METHOD,
                                                                        $unique_id_key, $image_file_name);
        $arguments_data_to_serialize = $galleryImageObject->getData();
        $arguments_data_to_serialize['sku'] = $sku;

        $serialized_arguments_value = serialize($arguments_data_to_serialize);
        $insert_data_array['serialized_arguments_object'] = $serialized_arguments_value;

        $number_of_inserted_rows = $this->_getWriteAdapter()->insert($this->getMainTable(), $insert_data_array);

        return $number_of_inserted_rows;
    }

    public function getTaskCode()
    {
        return $this->_task_code;
    }

    protected function _getImageSyncHelper()
    {
        if (is_null($this->_imageSyncHelper))
        {
            $this->_imageSyncHelper = Mage::helper('ReverbSync/sync_image');
        }

        return $this->_imageSyncHelper;
    }
}
