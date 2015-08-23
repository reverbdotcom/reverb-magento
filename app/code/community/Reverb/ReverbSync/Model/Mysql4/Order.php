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
}
