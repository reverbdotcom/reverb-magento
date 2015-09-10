<?php

require_once('Reverb/ReverbSync/controllers/Adminhtml/BaseController.php');
class Reverb_ReverbSync_Adminhtml_Orders_SyncController extends Reverb_ReverbSync_Adminhtml_BaseController
{
    const EXCEPTION_BULK_ORDERS_SYNC = 'An exception occurred while executing a Reverb bulk orders sync: %s';
    const SUCCESS_QUEUED_ORDERS_FOR_SYNC = 'Orders Sync Processed Successfully';

    public function bulkSyncAction()
    {
        try
        {
            Mage::helper('ReverbSync/orders_retrieval_creation')->queueReverbOrderSyncActions();

            Mage::helper('ReverbSync/orders_creation_task_processor')->processQueueTasks('order_creation');
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_BULK_ORDERS_SYNC, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
            Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));

            $redirectException = new Reverb_ReverbSync_Model_Exception_Redirect($error_message);
            $redirectException->prepareRedirect('adminhtml/orders_sync');
            throw $redirectException;
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__(self::SUCCESS_QUEUED_ORDERS_FOR_SYNC));
        $this->_redirect('adminhtml/orders_sync');
    }

    public function getBlockToShow()
    {
        $index_block = '/adminhtml_orders_index';
        return $this->getModuleBlockGroupname() . $index_block;
    }

    public function getControllerDescription()
    {
        return "Reverb Orders Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'sales/reverb_orders_sync';
    }
}
