<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 */

class Reverb_ReverbSync_Model_Mysql4_Category_Reverb_Magento_Xref extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('reverbSync/magento_reverb_category_xref','xref_id');
    }

    /**
     * @param array $magento_category_ids
     * @return array
     */
    public function getReverbCategoryUuidsByMagentoCategoryIds($magento_category_ids)
    {
        $table_name = $this->getMainTable();
        $readConnection = $this->getReadConnection();

        $where_clause = Reverb_ReverbSync_Model_Category_Reverb_Magento_Xref::MAGENTO_CATEGORY_ID_FIELD . ' in (?)';
        $select = $readConnection
                    ->select()
                    ->from($table_name, Reverb_ReverbSync_Model_Category_Reverb_Magento_Xref::REVERB_CATEGORY_UUID_FIELD)
                    ->where($where_clause, $magento_category_ids);

        $reverb_category_uuids = $readConnection->fetchCol($select);
        return $reverb_category_uuids;
    }
}
