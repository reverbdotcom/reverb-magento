<?php

/**
 * Reverb Report admin controller
 *
 * @category    Reverb
 * @package     Reverb_Reports
 */
class Reverb_Reports_Adminhtml_Reports_ReverbreportController
extends Mage_Adminhtml_Controller_Action {
  /**
   * init the reverbreport
   * @access protected
   * @return Reverb_Reports_Model_Reverbreport
   */
  protected function _initReverbreport() {
    $reverbreportId = (int)$this -> getRequest() -> getParam('id');
    $reverbreport = Mage::getModel('reverb_reports/reverbreport');
    if ($reverbreportId) {
      $reverbreport -> load($reverbreportId);
    }
    Mage::register('current_reverbreport', $reverbreport);
    return $reverbreport;
  }

  public function indexAction() {
    $this -> loadLayout();
    $this -> _title(Mage::helper('reverb_reports') -> __('Reverb Reports')) -> _title(Mage::helper('reverb_reports') -> __('Reverb Reports'));
    $this -> renderLayout();
  }

  public function gridAction() {
    $this -> loadLayout() -> renderLayout();
  }

  protected function _isAllowed() {
    return Mage::getSingleton('admin/session') -> isAllowed('reverb_reports/reverbreport');
  }

}
