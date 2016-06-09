<?php
/**
 * Author: Sean Dunagan (github: dunagan5887)
 * Date: 6/9/16
 */

/**
 * Class Reverb_ReverbSync_Model_Core_Config
 *
 * This class only exists to server migration reverbsync_setup/mysql4-upgrade-0.1.16-0.1.17.php
 * As of 6/9/2016 it serves no other purpose
 */
class Reverb_ReverbSync_Model_Mysql4_Core_Config extends Mage_Core_Model_Resource_Config
{
    /**
     * @param string $path
     * @return null|string
     */
    public function getConfigValue($path)
    {
        $table_name = $this->getMainTable();
        $readConnection = $this->getReadConnection();

        $select = $readConnection
                    ->select()
                    ->from($table_name, 'value')
                    ->where('path = ?', $path);

        $value_array = $readConnection->fetchCol($select);
        return (empty($value_array)) ? null : reset($value_array);
    }

    /**
     * @param string $path
     * @param string $value
     */
    public function updateConfigValue($path, $value)
    {
        $table_name = $this->getMainTable();
        $writeAdapter = $this->_getWriteAdapter();

        $set_clause = array('value' => $value);

        $where_clause = array('path=?' => $path);
        $writeAdapter->update($table_name, $set_clause, $where_clause);
    }
}