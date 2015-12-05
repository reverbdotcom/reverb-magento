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

        if (empty($product_reverb_category_objects_array))
        {
            if ($this->reverbCategoriesAreRequiredForListing())
            {
                $error_message = $this->__(self::ERROR_NO_REVERB_CATEGORIES_MAPPED);
                throw new Reverb_ReverbSync_Model_Exception_Category_Mapping($error_message);
            }
            // Return without modifying the $fieldsArray
            return $fieldsArray;
        }
        // If the user has mapped a product to both a subcategory and a toplevel category, the subcategory information wins.
        $sorted_reverb_categories_desc = $this->sortReverbCategoriesByLevelDescending($product_reverb_category_objects_array);
        $deepestReverbCategory = array_shift($sorted_reverb_categories_desc);
        $product_type_slug = $deepestReverbCategory->getData('reverb_product_type_slug');
        $category_slug = $deepestReverbCategory->getData('reverb_category_slug');

        if (empty($product_type_slug))
        {
            // If the user maps to a toplevel category (which has only "slug" and no "product_type_slug"), then the
            //      "slug" becomes the "product_type" in the request to reverb and "categories" is blank.
            $fieldsArray[self::CATEGORY_FIELD_NAME] = array();
            $fieldsArray[self::PRODUCT_TYPE_FIELD_NAME] = $category_slug;
            // We do not want to send multiple top-level categories. Since the first category shifted off of
            //  $sorted_reverb_categories_desc was a top-level category, we can assume that any additional categories
            //  would also be top-level categories
            return $fieldsArray;
        }
        else
        {
            /*
             * If the user maps to a subcategory (which has "slug" and "product_type_slug"), then "slug" goes into
             *  "categories" and "product_type_slug" goes into "product_type" in the request to reverb.
             */
            $fieldsArray[self::CATEGORY_FIELD_NAME] = array($category_slug);
            $fieldsArray[self::PRODUCT_TYPE_FIELD_NAME] = $product_type_slug;
        }

        // See if there is a second category to be mapped to
        if (!empty($sorted_reverb_categories_desc))
        {
            $secondDeepestReverbCategory = array_shift($sorted_reverb_categories_desc);
            if (Reverb_ReverbSync_Helper_Sync_Category::isReverbCategoryASubcategory($secondDeepestReverbCategory))
            {
                // If there are two subcategories, both should be sent in the "categories" field as long as they have
                //  the same product_type_slug
                $second_product_type_slug = $secondDeepestReverbCategory->getData('reverb_product_type_slug');
                if (!strcmp($second_product_type_slug, $product_type_slug)) {
                    $second_category_slug = $secondDeepestReverbCategory->getData('reverb_category_slug');
                    $fieldsArray[self::CATEGORY_FIELD_NAME][] = $second_category_slug;
                } else {
                    // If someone for some reason mapped their product into multiple subcategories with different parent
                    //      product types, we should just take the first one. So we do nothing here
                }
            }
        }

        return $fieldsArray;
    }

    public function sortReverbCategoriesByLevelDescending(array $product_reverb_category_objects_array)
    {
        usort($product_reverb_category_objects_array, 'Reverb_ReverbSync_Helper_Sync_Category::compareReverbCategoryLevelDescending');
        return $product_reverb_category_objects_array;
    }

    static public function compareReverbCategoryLevelDescending($reverbCategoryA, $reverbCategoryB)
    {
        $category_a_is_subcategory = Reverb_ReverbSync_Helper_Sync_Category::isReverbCategoryASubcategory($reverbCategoryA);
        $category_b_is_subcategory = Reverb_ReverbSync_Helper_Sync_Category::isReverbCategoryASubcategory($reverbCategoryB);

        return ($category_a_is_subcategory < $category_b_is_subcategory);
    }

    /**
     * Subcategories are identified by the fact that they ... have both "slug" and "product_type_slug".
     * They also have a " > " in their title, but that's not a reliable way to check for them being subcategories.
     *
     * @param $reverbCategoryA
     * @return int
     */
    static public function isReverbCategoryASubcategory($reverbCategoryA)
    {
        $reverb_product_type_slug = $reverbCategoryA->getData('reverb_product_type_slug');
        return (empty($reverb_product_type_slug) ? 0 : 1);
    }

    public function getReverbCategoryObjectsByProduct(Mage_Catalog_Model_Product $magentoProduct)
    {
        $magento_category_ids = $magentoProduct->getCategoryIds();
        $reverb_category_ids = Mage::getResourceSingleton('reverbSync/category_magento_reverb_mapping')
                                ->getReverbCategoryIdsByMagentoCategoryIds($magento_category_ids);

        if(empty($reverb_category_ids))
        {
            // Return an empty array
            return array();
        }

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
