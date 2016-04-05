<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/5/16
 */

class Reverb_ReverbSync_Helper_Sync_Category_Update extends Mage_Core_Helper_Abstract
{
    const EXCEPTION_CATEGORY_FETCH_API = 'An exception occurred while attempting to fetch the updated Reverb categories json: %s';
    const EXCEPTION_UPDATING_REVERB_CATEGORY = 'An exception occurred while persisting a reverb category in the Magento system: %s';
    const ERROR_CATEGORY_WITHOUT_UUID = 'The Reverb API returned a category without a uuid value: %s';

    protected $_categoryApiHelper = null;

    /**
     * Update the Reverb Category list in the system
     *
     * The calling block is expected to catch exceptions thrown by this method
     *
     * @return array
     * @throws Reverb_ReverbSync_Model_Exception_Category_Mapping
     */
    public function updateReverbCategoriesFromApi()
    {
        try
        {
            // Attempt to retrieve the updated Reverb categories list
            $reverb_categories_as_array = $this->_getCategoryApiHelper()->executeCategoryAPIFetch();
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_CATEGORY_FETCH_API, $e->getMessage());
            $exceptionToThrow = new Reverb_ReverbSync_Model_Exception_Category_Mapping($error_message);
            throw $exceptionToThrow;
        }

        foreach($reverb_categories_as_array as $reverb_category_data_array)
        {
            try
            {
                $this->_updateReverbCategoryData($reverb_category_data_array);
            }
            catch(Exception $e)
            {
                // Don't rethrow this exception since other categories may be updated successfully
                $error_message = $this->__(self::EXCEPTION_UPDATING_REVERB_CATEGORY, $e->getMessage());
                $this->_logError($error_message);
            }
        }
    }

    /**
     * @param array $reverb_category_data_array
     * @throws Exception
     * @throws Reverb_ReverbSync_Model_Exception_Category_Mapping
     */
    protected function _updateReverbCategoryData(array $reverb_category_data_array)
    {
        if ((!isset($reverb_category_data_array['uuid'])) || empty($reverb_category_data_array['uuid']))
        {
            $encoded_category_data = json_encode($reverb_category_data_array, JSON_UNESCAPED_SLASHES);
            $error_message = $this->__(self::ERROR_CATEGORY_WITHOUT_UUID, $encoded_category_data);
            throw new Reverb_ReverbSync_Model_Exception_Category_Mapping($error_message);
        }

        $uuid = $reverb_category_data_array['uuid'];
        $reverbCategory = Mage::getModel('reverbSync/category_reverb')->loadByUuid($uuid);
        if (!(is_object($reverbCategory) && $reverbCategory->getId()))
        {
            $reverbCategory = Mage::getModel('reverbSync/category_reverb');
            $reverbCategory->setUuid($uuid);
        }
        /* @var $reverbCategory Reverb_ReverbSync_Model_Category_Reverb */
        $full_name = isset($reverb_category_data_array['full_name']) ? $reverb_category_data_array['full_name'] : '';
        $reverbCategory->setName($full_name);
        $slug = isset($reverb_category_data_array['slug']) ? $reverb_category_data_array['slug'] : '';
        $reverbCategory->setReverbCategorySlug($slug);
        $product_type_slug = isset($reverb_category_data_array['product_type_slug']) ? $reverb_category_data_array['product_type_slug'] : '';
        $reverbCategory->setReverbProductTypeSlug($product_type_slug);
        $parent_uuid = isset($reverb_category_data_array['root_uuid']) ? $reverb_category_data_array['root_uuid'] : null;
        $reverbCategory->setParentUuidField($parent_uuid);
        $reverbCategory->save();
    }

    /**
     * @param $error_message
     */
    protected function _logError($error_message)
    {
        Mage::getSingleton('reverbSync/log')->logCategoryMappingError($error_message);
    }

    /**
     * @return Reverb_ReverbSync_Helper_Api_Adapter_Category
     */
    protected function _getCategoryApiHelper()
    {
        if (is_null($this->_categoryApiHelper))
        {
            $this->_categoryApiHelper = Mage::helper('ReverbSync/api_adapter_category');
        }

        return $this->_categoryApiHelper;
    }
}
