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

}
