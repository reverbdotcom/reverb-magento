<?php

/**
 * Reverb Report resource model
 *
 * @category    Reverb
 * @package     Reverb_Reports

 */
class Reverb_Reports_Model_Resource_Reverbreport
extends Mage_Core_Model_Resource_Db_Abstract {
  /**
   * constructor
   * @access public
   */
  public function _construct() {
    $this -> _init('reverb_reports/reverbreport', 'entity_id');
  }

    public function deleteAllReverbReportRows()
    {
        $rows_deleted = $this->_getWriteAdapter()->delete($this->getMainTable());
        return $rows_deleted;
    }

    public function deleteSuccessfulSyncs()
    {
        $where_condition_array = array('status=?' => Reverb_Reports_Model_Reverbreport::STATUS_SUCCESS);
        $rows_deleted = $this->_getWriteAdapter()->delete($this->getMainTable(), $where_condition_array);
        return $rows_deleted;
    }
}
