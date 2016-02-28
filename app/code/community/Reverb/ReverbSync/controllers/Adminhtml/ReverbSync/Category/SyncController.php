<?php

/**
 *
 * @category    Reverb
 * @package     Reverb_ReverbSync
 * @author      Sean Dunagan
 * @author      Timur Zaynullin <zztimur@gmail.com>
 */

require_once('Reverb/ReverbSync/controllers/Adminhtml/BaseController.php');
class Reverb_ReverbSync_Adminhtml_ReverbSync_Category_SyncController extends Reverb_ReverbSync_Adminhtml_BaseController
{
    const ERROR_SUBMISSION_NOT_POST = 'There was an error with your submission. Please try again.';
    const EXCEPTION_CATEGORY_MAPPING = 'An error occurred while attempting to set the Reverb-Magento category mapping: %s';

    protected $_categorySyncHelper = null;
    protected $_adminHelper = null;

    public function saveAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $error_message = self::ERROR_SUBMISSION_NOT_POST;
            $this->_getAdminHelper()->throwRedirectException($error_message);
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
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        $this->_redirect('*/*/index');
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
