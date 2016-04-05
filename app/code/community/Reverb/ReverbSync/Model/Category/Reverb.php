<?php
/**
 * Author: Sean Dunagan
 * Created: 10/26/15
 */

class Reverb_ReverbSync_Model_Category_Reverb extends Mage_Core_Model_Abstract
{
    const NAME_FIELD = 'name';
    const PRODUCT_TYPE_SLUG_FIELD = 'reverb_product_type_slug';
    const CATEGORY_SLUG_FIELD = 'reverb_category_slug';
    const UUID_FIELD = 'uuid';
    const PARENT_UUID_FIELD = 'parent_uuid';

    protected $_json_to_orm_field_mapping_array = array(
        'name' => 'name',
        'description' => 'description',
        'slug' => 'reverb_category_slug',
        'product_type_slug' => 'reverb_product_type_slug',
        'id' => 'reverb_category_id'
    );

    public function loadByUuid($uuid)
    {
        return $this->load($uuid, self::UUID_FIELD);
    }

    protected function _construct()
    {
        $this->_init('reverbSync/category_reverb');
    }

    public function getUuid()
    {
        return $this->getData(self::UUID_FIELD);
    }

    public function setUuid($uuid)
    {
        return $this->setData(self::UUID_FIELD, $uuid);
    }

    public function getParentUuidField()
    {
        return $this->getData(self::PARENT_UUID_FIELD);
    }

    public function setParentUuidField($parent_uuid)
    {
        return $this->getData(self::PARENT_UUID_FIELD, $parent_uuid);
    }

    public function convertJsonObjectArrayToORMDataArray(array $jsonObject, $include_primary_key = false)
    {
        $orm_data_array = array();
        $orm_data_array['name'] = $jsonObject->name;
        $orm_data_array['description'] = $jsonObject->description;
        $orm_data_array['reverb_category_slug'] = $jsonObject->slug;
        if ($include_primary_key)
        {
            $orm_data_array['reverb_category_id'] = $jsonObject->id ;
        }

        $orm_data_array['reverb_product_type_slug'] = isset($jsonObject->product_type_slug)
                                                        ? $jsonObject->product_type_slug : null;

        return $orm_data_array;
    }
}
