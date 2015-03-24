<?php

/**
 * Reverb Report model
 *
 * @category    Reverb
 * @package     Reverb_Reports

 */
class Reverb_Reports_Model_Reverbreport
extends Mage_Core_Model_Abstract {
  /**
   * Entity code.
   * Can be used as part of method name for entity processing
   */
  const ENTITY = 'reverb_reports_reverbreport';
  const CACHE_TAG = 'reverb_reports_reverbreport';
  /**
   * Prefix of model events names
   * @var string
   */
  protected $_eventPrefix = 'reverb_reports_reverbreport';

  protected $_eventObject = 'reverbreport';

  public function _construct() {
    parent::_construct();
    $this -> _init('reverb_reports/reverbreport');
  }

}
