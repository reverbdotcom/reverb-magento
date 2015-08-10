<?php

class Reverb_ReverbSync_Model_Catalog_Resource_Product extends Mage_Catalog_Model_Resource_Product
{
    public function getAllProductIdsArray()
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from($this->getEntityTable(), 'entity_id');

        $all_product_ids = $adapter->fetchCol($select);

        return $all_product_ids;
    }
}
