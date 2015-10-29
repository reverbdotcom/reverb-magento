<?php
/**
 * Author: Sean Dunagan
 * Created: 10/26/15
 */

class Reverb_ReverbSync_Helper_Sync_Category extends Mage_Core_Helper_Abstract
{
    const ERROR_NO_REVERB_CATEGORIES_MAPPED = 'Skipping; no category map';
    const FORM_ELEMENT_FIELD_NAME_TEMPLATE = '%s[%s]';
    const REVERB_CATEGORY_MAP_ELEMENT_NAME = 'reverb_category_map';
    const NO_CATEGORY_CHOSEN_OPTION = 'none';

    const PRODUCT_TYPE_FIELD_NAME = 'product_type';
    const CATEGORY_FIELD_NAME = 'categories';

    const REVERB_CATEGORY_MAPPING_REQUIRED_FOR_LISTING = 'ReverbSync/reverbDefault/require_reverb_category_definition';

    protected $_moduleName = 'ReverbSync';

    protected $_reverb_category_options_array = null;

    public function getMagentoReverbCategoryMapElementArrayName()
    {
        return self::REVERB_CATEGORY_MAP_ELEMENT_NAME;
    }

    public function getReverbCategoryMapFormElementName($field)
    {
        return sprintf(self::FORM_ELEMENT_FIELD_NAME_TEMPLATE, self::REVERB_CATEGORY_MAP_ELEMENT_NAME, $field);
    }

    public function getNoCategoryChosenOption()
    {
        return self::NO_CATEGORY_CHOSEN_OPTION;
    }

    public function processMagentoReverbCategoryMapping(array $reverb_magento_category_mapping)
    {
        // Filter out all values in the array which are set to the NO_CATEGORY_CHOSEN_OPTION value
        $defined_category_mapping
            = array_filter($reverb_magento_category_mapping,
                             'Reverb_ReverbSync_Helper_Sync_Category::filter_out_no_category_chosen_option');

        if (!empty($defined_category_mapping))
        {
            Mage::getResourceSingleton('reverbSync/category_magento_reverb_mapping')
                ->redefineCategoryMapping($defined_category_mapping);
        }
    }

    static public function filter_out_no_category_chosen_option($mapped_value)
    {
        return strcmp($mapped_value, self::NO_CATEGORY_CHOSEN_OPTION);
    }

    public function addCategoriesToListingFieldsArray(array $fieldsArray, Mage_Catalog_Model_Product $product)
    {
        $product_reverb_category_objects_array = $this->getReverbCategoryObjectsByProduct($product);

        if (empty($product_reverb_category_objects_array) && $this->reverbCategoriesAreRequiredForListing())
        {
            $error_message = $this->__(self::ERROR_NO_REVERB_CATEGORIES_MAPPED);
            throw new Reverb_ReverbSync_Model_Exception_Category_Mapping($error_message);
        }

        $category_slug_array = array();
        $product_type_slug = null;

        foreach($product_reverb_category_objects_array as $reverbCategory)
        {
            $category_slug_array[] = $reverbCategory->getReverbCategorySlug();
            if (empty($product_type_slug))
            {
                $product_type_slug = $reverbCategory->getReverbProductTypeSlug();
            }
        }

        // We expect category slug to be populated, but check just in case
        if (!empty($category_slug_array))
        {
            $fieldsArray[self::CATEGORY_FIELD_NAME] = $category_slug_array;
        }
        // If the only categories mapped are top-level Reverb categories, there will be no product type slug
        if (!empty($product_type_slug))
        {
            $fieldsArray[self::PRODUCT_TYPE_FIELD_NAME] = $product_type_slug;
        }

        return $fieldsArray;
    }

    public function getReverbCategoryObjectsByProduct(Mage_Catalog_Model_Product $magentoProduct)
    {
        $magento_category_ids = $magentoProduct->getCategoryIds();
        $reverb_category_ids = Mage::getResourceSingleton('reverbSync/category_magento_reverb_mapping')
                                ->getReverbCategoryIdsByMagentoCategoryIds($magento_category_ids);

        $reverbCategoryCollection = Mage::getModel('reverbSync/category_reverb')
                                        ->getCollection()
                                        ->addReverbCategoryIdFilter($reverb_category_ids);

        return $reverbCategoryCollection->getItems();
    }

    public function reverbCategoriesAreRequiredForListing()
    {
        return Mage::getStoreConfig(self::REVERB_CATEGORY_MAPPING_REQUIRED_FOR_LISTING);
    }

    public function getReverbCategorySelectOptionsArray()
    {
        $reverb_category_select_options_array = array();
        $reverb_category_select_options_array[self::NO_CATEGORY_CHOSEN_OPTION] = '';
        $reverb_categories = $this->getReverbCategoriesArray();

        foreach($reverb_categories as $reverbCategory)
        {
            $reverb_category_select_options_array[$reverbCategory->getId()] = $reverbCategory->getName();
        }

        return $reverb_category_select_options_array;
    }

    public function getReverbCategoriesArray()
    {
        if (is_null($this->_reverb_category_options_array))
        {
            $reverbCategoryCollection = Mage::getModel('reverbSync/category_reverb')
                                            ->getCollection();

            $this->_reverb_category_options_array = $reverbCategoryCollection->getItems();
        }

        return $this->_reverb_category_options_array;
    }
}
