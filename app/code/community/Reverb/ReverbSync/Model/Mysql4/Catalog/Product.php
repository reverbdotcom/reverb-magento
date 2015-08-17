<?php
/**
 * Author: Sean Dunagan
 * Created: 8/16/15
 */

class Reverb_ReverbSync_Model_Mysql4_Catalog_Product extends Mage_Catalog_Model_Resource_Product
{
    public function getSkuById($product_id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->getEntityTable(), 'sku')
            ->where('entity_id = :entity_id');

        $bind = array(':entity_id' => (string)$product_id);

        return $adapter->fetchOne($select, $bind);
    }
} 