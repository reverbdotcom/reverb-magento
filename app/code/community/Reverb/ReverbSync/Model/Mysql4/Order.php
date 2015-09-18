<?php
/**
 * Author: Sean Dunagan
 * Created: 8/22/15
 */

class Reverb_ReverbSync_Model_Mysql4_Order extends Mage_Sales_Model_Mysql4_Order
{
    public function getMagentoOrderEntityIdByReverbOrderNumber($reverb_order_number)
    {
        $table_name = $this->getMainTable();
        $readConnection = $this->getReadConnection();

        $select = $readConnection
                    ->select()
                    ->from($table_name, array('entity_id'))
                    ->where('reverb_order_id = ?', $reverb_order_number);

        $order_entity_id = $readConnection->fetchOne($select);

        return $order_entity_id;
    }

    public function updateReverbOrderStatusByMagentoEntityId($magento_entity_id, $reverb_order_status)
    {
        $update_bind_array = array('reverb_order_status' => $reverb_order_status);
        $where_conditions_array = array('entity_id=?' => $magento_entity_id);

        $rows_updated = $this->_getWriteAdapter()
                            ->update($this->getMainTable(), $update_bind_array, $where_conditions_array);
        return $rows_updated;
    }

    public function setReverbStoreNameByReverbOrderId($reverb_order_id, $store_name)
    {
        $update_bind_array = array('store_name' => $store_name);
        $where_conditions_array = array('reverb_order_id=?' => $reverb_order_id);

        $rows_updated = $this->_getWriteAdapter()
                            ->update($this->getMainTable(), $update_bind_array, $where_conditions_array);
        return $rows_updated;
    }
}
