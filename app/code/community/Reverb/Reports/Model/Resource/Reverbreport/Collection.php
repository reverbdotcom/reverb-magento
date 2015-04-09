<?php

/**
 * Reverb Report collection resource model
 *
 * @category    Reverb
 * @package     Reverb_Reports
 */
class Reverb_Reports_Model_Resource_Reverbreport_Collection
extends Mage_Core_Model_Resource_Db_Collection_Abstract {

  protected function _construct() {
    parent::_construct();
    $this -> _init('reverb_reports/reverbreport');
  }

}
