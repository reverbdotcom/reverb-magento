<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 * Class Reverb_ReverbSync_Model_Import_Category_Uuid_Mapping
 */
class Reverb_ReverbSync_Model_Import_Category_Uuid_Mapping
    extends Reverb_Io_Model_Import_Csv_Abstract
    implements Reverb_Io_Model_Import_Csv_Interface
{
    const ERROR_EMPTY_FIELD_VALUE = 'No value was provided for field(s) %s';
    const EXCEPTION_IMPORTING_MAPPING_ROW = 'An exception occurred while attempting to map the Reverb category uuid for row %s with data %s: %s';
    const EXCEPTION_SAVING_MAPPING_TO_DATABASE = 'An exception occurred while attempting to map Reverb category uuid %s to Magento category entity id %s: %s';

    const UUID_FIELD = 'uuid';
    const PRODUCT_TYPE_SLUG_FIELD = 'root_category_slug';
    const CATEGORY_SLUG_FIELD = 'category_slug';
    const NAME_FIELD = 'description';

    const EXPECTED_FILENAME = 'reverb_category_slug_uuid_mapping.csv';

    protected $_error_log_file = 'reverb_category_uuid_to_slug_mapping';
    protected $_suppress_invalid_filename_errors = true;
    protected $_required_header_rows = array(self::UUID_FIELD, self::PRODUCT_TYPE_SLUG_FIELD, self::CATEGORY_SLUG_FIELD, self::NAME_FIELD);

    protected $_translationHelper = null;

    /**
     * @param Varien_Object $rowData
     * @param int $row_num
     */
    public function importDataRow($rowData, $row_num)
    {
        try
        {
            // Need to get the existing Reverb category row based on the data in the row
            $reverbCategory = $this->_getReverbCategoryBySlugs($rowData);
            // We will want to query any existing category mappings to make sure they are preserved
            $query_category_mappings_to_preserve = true;
            $reverb_category_uuid = $rowData->getData(self::UUID_FIELD);

            if (!is_null($reverbCategory))
            {
                // Update the existing Reverb Category Row
                $reverbCategory->setUuid($reverb_category_uuid);

                $category_name = $rowData->getData(self::NAME_FIELD);
                $reverbCategory->setName($category_name);
            }
            else
            {
                // Create a new Reverb Category Row
                $reverbCategory = Mage::getModel('reverbSync/category_reverb');
                $reverbCategory->setData(Reverb_ReverbSync_Model_Category_Reverb::NAME_FIELD,
                                        $rowData->getData(self::NAME_FIELD));
                $reverbCategory->setData(Reverb_ReverbSync_Model_Category_Reverb::PRODUCT_TYPE_SLUG_FIELD,
                                        $rowData->getData(self::PRODUCT_TYPE_SLUG_FIELD));
                $reverbCategory->setData(Reverb_ReverbSync_Model_Category_Reverb::CATEGORY_SLUG_FIELD,
                                        $rowData->getData(self::CATEGORY_SLUG_FIELD));
                $reverbCategory->setData(Reverb_ReverbSync_Model_Category_Reverb::UUID_FIELD, $reverb_category_uuid);
                // Since we are going to create this category for the first time, there won't be any category
                //      mappings to it in the system
                $query_category_mappings_to_preserve = false;
            }

            // Update the existing Reverb category with the data in the row
            $reverbCategory->save();

            if ($query_category_mappings_to_preserve)
            {
                // Create a mapping in the new Reverb Category xref mapping table if a mapping exists
                $reverb_category_id = $reverbCategory->getId();
                $legacy_reverb_magento_category_mappings_to_preserve
                    = $this->_getMagentoCategoriesMappedToReverbCategory($reverb_category_id);
                if (!empty($legacy_reverb_magento_category_mappings_to_preserve))
                {
                    foreach($legacy_reverb_magento_category_mappings_to_preserve as $categoryMappingObject)
                    {
                        /* @var $categoryMappingObject Reverb_ReverbSync_Model_Category_Magento_Reverb_Mapping */
                        $magento_category_id = $categoryMappingObject->getMagentoCategoryId();
                        try
                        {
                            $this->_createNewCategoryMapping($reverb_category_uuid, $magento_category_id);
                        }
                        catch(Exception $e)
                        {
                            $error_message = $this->_getTranslationHelper()
                                                  ->__(self::EXCEPTION_SAVING_MAPPING_TO_DATABASE, $reverb_category_uuid,
                                                       $magento_category_id, $e->getMessage());
                            $this->logError($error_message);
                        }
                    }
                }
            }
        }
        catch(Exception $e)
        {
            $row_data_array = $rowData->getData();
            $imploded_data_array = implode(', ', $row_data_array);
            $error_message = $this->_getTranslationHelper()
                                  ->__(self::EXCEPTION_IMPORTING_MAPPING_ROW, $row_num, $imploded_data_array,
                                      $e->getMessage());
            $this->logError($error_message);
        }
    }

    /**
     * @param string $reverb_category_uuid - Reverb category UUID from the import csv data file
     * @param int $magento_category_id - Entity id for the Magento category
     */
    protected function _createNewCategoryMapping($reverb_category_uuid, $magento_category_id)
    {
        $newCategoryMappingObject = Mage::getModel('reverbSync/category_reverb_magento_xref');
        /* @var $newCategoryMappingObject Reverb_ReverbSync_Model_Category_Reverb_Magento_Xref */
        $newCategoryMappingObject->setData('reverb_category_uuid', $reverb_category_uuid);
        $newCategoryMappingObject->setData('magento_category_id', $magento_category_id);
        $newCategoryMappingObject->save();
    }

    /**
     * @param int $reverb_category_id
     * @return array
     */
    protected function _getMagentoCategoriesMappedToReverbCategory($reverb_category_id)
    {
        $legacy_reverb_magento_category_mappings = Mage::getModel('reverbSync/category_magento_reverb_mapping')
                                                        ->getCollection()
                                                        ->addFieldToFilter('reverb_category_id', $reverb_category_id)
                                                        ->getItems();

        return $legacy_reverb_magento_category_mappings;
    }

    public function isDataValid($rowData, $row_num)
    {
        $empty_fields = array();

        // Verify that a value was provided for all fields in the row
        foreach($this->getRequiredHeaders() as $header_field)
        {
            $field_value = $rowData->getData($header_field);
            if (empty($field_value))
            {
                $empty_fields[] = $header_field;
            }
        }

        if (!empty($empty_fields))
        {
            $imploded_empty_fields = implode(', ', $empty_fields);
            $error_message = $this->_getTranslationHelper()->__(self::ERROR_EMPTY_FIELD_VALUE, $imploded_empty_fields);
            throw new Reverb_ReverbSync_Model_Exception_Category_Mapping($error_message);
        }

        return true;
    }

    /**
     * @param Varien_Object $rowData
     * @return Reverb_ReverbSync_Model_Category_Reverb|null
     * @throws Reverb_ReverbSync_Model_Exception_Category_Mapping
     */
    protected function _getReverbCategoryBySlugs($rowData)
    {
        $reverb_product_type_slug = $rowData->getData(self::PRODUCT_TYPE_SLUG_FIELD);
        $reverb_category_slug = $rowData->getData(self::CATEGORY_SLUG_FIELD);

        $reverbCategory = Mage::getModel('reverbSync/category_reverb')
                            ->getCollection()
                            ->addFieldToFilter('reverb_product_type_slug', $reverb_product_type_slug)
                            ->addFieldToFilter('reverb_category_slug', $reverb_category_slug)
                            ->getFirstItem();

        /* @var $reverbCategory Reverb_ReverbSync_Model_Category_Reverb */
        if ((!is_object($reverbCategory)) || (!$reverbCategory->getId()))
        {
            // The category may have a NULL value for reverb_product_type_slug
            $reverbCategory = Mage::getModel('reverbSync/category_reverb')
                                ->getCollection()
                                ->addFieldToFilter('reverb_product_type_slug', array('null' => true))
                                ->addFieldToFilter('reverb_category_slug', $reverb_category_slug)
                                ->getFirstItem();

            /* @var $reverbCategory Reverb_ReverbSync_Model_Category_Reverb */
            if ((!is_object($reverbCategory)) || (!$reverbCategory->getId()))
            {
                return null;
            }
        }

        return $reverbCategory;
    }

    /**
     * @return array
     */
    public function getRequiredHeaders()
    {
        return $this->_required_header_rows;
    }

    /**
     * @return string
     */
    public function getImportDirectory()
    {
        return Mage::getModuleDir('data', 'Reverb_ReverbSync');
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function validateFilename($filename)
    {
        return (!strcmp(self::EXPECTED_FILENAME, $filename));
    }

    /**
     * @return Reverb_ReverbSync_Helper_Data
     */
    protected function _getTranslationHelper()
    {
        if (is_null($this->_translationHelper))
        {
            $this->_translationHelper = Mage::helper('ReverbSync');
        }

        return $this->_translationHelper;
    }
}
