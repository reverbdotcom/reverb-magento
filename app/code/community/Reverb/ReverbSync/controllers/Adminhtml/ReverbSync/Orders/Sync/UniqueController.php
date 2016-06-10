<?php
/**
 * @category    Reverb
 * @package     Reverb_ReverbSync
 * @author      Sean Dunagan
 * @author      Timur Zaynullin <zztimur@gmail.com>
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/Unique/IndexController.php');

/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Class Reverb_ReverbSync_Adminhtml_ReverbSync_Orders_Sync_UniqueController
 */
class Reverb_ReverbSync_Adminhtml_ReverbSync_Orders_Sync_UniqueController
    extends Reverb_ProcessQueue_Adminhtml_ProcessQueue_Unique_IndexController
{
    const EXCEPTION_SYNC_SHIPMENT_TRACKING = 'An error occurred while attempting to sync shipment tracking data with Reverb: %s';
    const NOTICE_SYNC_SHIPMENT_TRACKING = 'The attempt to sync shipment tracking data with Reverb has completed';

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_orders_unique_index'))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_orders_task_unique_index'))
            ->renderLayout();
    }

    public function syncShipmentTrackingAction()
    {
        try
        {
            $this->_getTaskProcessor()->processQueueTasks(Reverb_ReverbSync_Model_Sync_Shipment_Tracking::JOB_CODE);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_SYNC_SHIPMENT_TRACKING, $e->getMessage());
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        Mage::getSingleton('adminhtml/session')->addNotice($this->__(self::NOTICE_SYNC_SHIPMENT_TRACKING));
        $this->_redirect('*/*/index');
    }

    public function canAdminUpdateStatus()
    {
        return Mage::helper('ReverbSync/orders_sync')->canAdminChangeOrderCreationSyncStatus();
    }

    public function getEditBlockClassname()
    {
        return 'ReverbSync/adminhtml_orders_task_unique_edit';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_orders_task_unique_index';
    }

    public function getControllerDescription()
    {
        return "Reverb Shipment Tracking Creation Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'reverb/reverb_order_unique_task_sync';
    }

    public function getModuleInstanceDescription()
    {
        return 'Reverb Shipment Tracking Sync Tasks';
    }

    public function getObjectDescription()
    {
        return 'Sync Task';
    }

    public function getIndexActionsController()
    {
        return 'ReverbSync_orders_sync_unique';
    }

    protected function _getModuleBlockGroupname()
    {
        return 'ReverbSync';
    }

    public function getObjectParamName()
    {
      return 'task_id';
    }
}
