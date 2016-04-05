<?php
/**
 * Author: Sean Dunagan
 * Created: 9/11/15
 */

require_once('Reverb/ReverbSync/controllers/Adminhtml/BaseController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Category_SyncController extends Reverb_ReverbSync_Adminhtml_BaseController
{
    const BULK_SYNC_EXCEPTION = 'An uncaught exception occurred while executing the Reverb Bulk Product Sync via the admin panel: %s';
    const SUCCESS_BULK_SYNC_COMPLETED = 'Reverb Bulk product sync process completed.';
    const SUCCESS_BULK_SYNC_QUEUED_UP = '%s products have been queued to be synced with Reverb';
    const EXCEPTION_STOP_BULK_SYNC = 'An exception occurred while attempting to stop all reverb listing sync tasks: %s';
    const SUCCESS_STOPPED_LISTING_SYNCS = 'Stopped all pending Reverb Listing Sync tasks';
    const ERROR_SUBMISSION_NOT_POST = 'There was an error with your submission. Please try again.';
    const EXCEPTION_CATEGORY_MAPPING = 'An error occurred while attempting to set the Reverb-Magento category mapping: %s';
    const EXCEPTION_UPDATING_REVERB_CATEGORIES = 'An exception occurred while updating the Reverb categories in the system: %s';
    const SUCCESS_UPDATED_LISTINGS = 'Reverb category update completed';

    protected $_categorySyncHelper = null;
    protected $_adminHelper = null;

    public function saveAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $error_message = self::ERROR_SUBMISSION_NOT_POST;
            $this->_setSessionErrorAndRedirect($error_message);
        }

        $post_array = $this->getRequest()->getPost();

        try
        {
            $category_map_form_element_name = $this->_getCategorySyncHelper()
                                                    ->getMagentoReverbCategoryMapElementArrayName();
            $category_mapping_array = isset($post_array[$category_map_form_element_name])
                                        ? $post_array[$category_map_form_element_name] : null;
            if (!is_array($category_mapping_array) || empty($category_mapping_array))
            {
                // This shouldn't occur, but account for the fact where it does
                $error_message = self::ERROR_SUBMISSION_NOT_POST;
                throw new Exception($error_message);
            }

            $this->_getCategorySyncHelper()->processMagentoReverbCategoryMapping($category_mapping_array);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_CATEGORY_MAPPING, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logCategoryMappingError($error_message);
            $this->_setSessionErrorAndRedirect($error_message);
        }

        $this->_redirect('*/*/index');
    }

    public function updateCategoriesAction()
    {
        try
        {
            $categoryUpdateSyncHelper = Mage::helper('ReverbSync/sync_category_update');
            /* @var $categoryUpdateSyncHelper Reverb_ReverbSync_Helper_Sync_Category_Update */
            $categoryUpdateSyncHelper->updateReverbCategoriesFromApi();
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_UPDATING_REVERB_CATEGORIES, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logCategoryMappingError($error_message);
            $this->_setSessionErrorAndRedirect($error_message);
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__(self::SUCCESS_UPDATED_LISTINGS));

        $this->_redirect('*/*/index');
    }

    /**
     * @param $error_message
     * @throws Reverb_ReverbSync_Controller_Varien_Exception
     */
    protected function _setSessionErrorAndRedirect($error_message)
    {
        Mage::getSingleton('adminhtml/session')->addError($this->__($error_message));
        $exception = new Reverb_ReverbSync_Controller_Varien_Exception($error_message);
        $exception->prepareRedirect('*/*/index');
        throw $exception;
    }

    public function getUriPathForAction($action)
    {
        $uri_path = sprintf('%s/%s/%s', 'adminhtml', 'ReverbSync_category_sync', $action);
        return $uri_path;
    }

    public function getBlockToShow()
    {
        return $this->getModuleBlockGroupname() . '/adminhtml_category_edit';
    }

    public function getControllerDescription()
    {
        return "Reverb Category Sync";
    }

    public function getControllerActiveMenuPath()
    {
        return 'reverb/reverb_category_sync';
    }

    public function getModuleBlockGroupname()
    {
        return 'ReverbSync';
    }

    public function getObjectParamName()
    {
        return 'reverb_category_map';
    }

    /**
     * @return Reverb_ReverbSync_Helper_Sync_Category
     */
    protected function _getCategorySyncHelper()
    {
        if (is_null($this->_categorySyncHelper))
        {
            $this->_categorySyncHelper = Mage::helper('ReverbSync/sync_category');
        }

        return $this->_categorySyncHelper;
    }
}
