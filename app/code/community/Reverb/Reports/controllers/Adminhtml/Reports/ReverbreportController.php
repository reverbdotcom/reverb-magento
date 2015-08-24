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

    public function indexAction()
    {
        $module_block_classname = $this->getBlockToShow();

        $this -> loadLayout();
        $this -> _title(Mage::helper('reverb_reports') -> __('Reverb Reports')) -> _title(Mage::helper('reverb_reports') -> __('Reverb Reports'));
        $this->_addContent($this->getLayout()->createBlock($module_block_classname));

        $gridBlock = $this->getLayout()->createBlock('reverb_reports/adminhtml_reverbreport');
        $gridBlock->setNameInLayout('reverbreport');
        $this->_addContent($gridBlock);

        $this -> renderLayout();
    }

    public function getBlockToShow()
    {
        $are_product_syncs_pending = $this->areProductSyncsPending();
        $index_block = $are_product_syncs_pending ? '/adminhtml_index_syncing' : '/adminhtml_index';
        return "ReverbSync" . $index_block;
    }

    public function areProductSyncsPending()
    {
        $outstandingListingSyncTasksCollection = Mage::helper('reverb_process_queue/task_processor')
                                                    ->getQueueTasksForProgressScreen('listing_sync');
        $outstanding_tasks_array = $outstandingListingSyncTasksCollection->getItems();

        return (!empty($outstanding_tasks_array));
    }

  public function gridAction() {
    $this -> loadLayout() -> renderLayout();
  }

  protected function _isAllowed() {
    return Mage::getSingleton('admin/session') -> isAllowed('reverb_reports/reverbreport');
  }

}
