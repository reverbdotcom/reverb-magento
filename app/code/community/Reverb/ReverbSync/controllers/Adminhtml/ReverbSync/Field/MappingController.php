<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/18/16
 */

class Reverb_ReverbSync_Adminhtml_ReverbSync_Field_MappingController
    extends Reverb_Base_Controller_Adminhtml_Form_Abstract
    implements Reverb_Base_Controller_Adminhtml_Form_Interface
{
    const ERROR_FIELD_ALREADY_MAPPED_IN_SYSTEM = "Reverb Field `%s` is already mapped to Magento attribute `%s`";
    const ERROR_ATTRIBUTE_DOES_NOT_EXIST_IN_THE_SYSTEM = 'Product attribute `%s` does not exist in the system';

    /**
     * @param Reverb_ReverbSync_Model_Field_Mapping $objectToUpdate
     * @param array $posted_object_data
     * @return Reverb_ReverbSync_Model_Field_Mapping
     * @throws Exception
     */
    public function validateDataAndUpdateObject($objectToUpdate, $posted_object_data)
    {
        $objectToUpdate->addData($posted_object_data);

        $this->_validateMagentoAttributeExistsInSystem($objectToUpdate);
        $this->_validateReverbFieldMappingDoesNotAlreadyExist($objectToUpdate);

        return $objectToUpdate;
    }

    /**
     * @param Reverb_ReverbSync_Model_Field_Mapping $objectToCreate
     * @param array $posted_object_data
     * @return Reverb_ReverbSync_Model_Field_Mapping
     * @throws Exception
     */
    public function validateDataAndCreateObject($objectToCreate, $posted_object_data)
    {
        $objectToCreate->setData($posted_object_data);

        $this->_validateMagentoAttributeExistsInSystem($objectToCreate);
        $this->_validateReverbFieldMappingDoesNotAlreadyExist($objectToCreate);

        return $objectToCreate;
    }

    /**
     * @param Reverb_ReverbSync_Model_Field_Mapping $mappingObjectToValidate
     * @throws Exception
     */
    protected function _validateMagentoAttributeExistsInSystem($mappingObjectToValidate)
    {
        $mapped_magento_attribute = $mappingObjectToValidate->getMagentoAttributeCode();
        $attributeModel = Mage::getModel('eav/entity_attribute')
                            ->loadByCode(Mage_Catalog_Model_Product::ENTITY, $mapped_magento_attribute);

        if ((!is_object($attributeModel)) || (!$attributeModel->getId()))
        {
            $error_message = $this->__(self::ERROR_ATTRIBUTE_DOES_NOT_EXIST_IN_THE_SYSTEM, $mapped_magento_attribute);
            throw new Exception($error_message);
        }
    }

    /**
     * @param Reverb_ReverbSync_Model_Field_Mapping $mappingObjectToValidate
     */
    protected function _validateReverbFieldMappingDoesNotAlreadyExist($mappingObjectToValidate)
    {
        // Get the reverb field defined on the object
        $reverb_api_field = $mappingObjectToValidate->getReverbApiField();
        // Attempt to load a mapping from the database with this reverb field
        $mappedReverbFieldObject = Mage::getModel('reverbSync/field_mapping')
                                        ->load($reverb_api_field, 'reverb_api_field');
        /* @var $mappedReverbFieldObject Reverb_ReverbSync_Model_Field_Mapping */
        // Check whether the $mappingObjectToValidate passed in already exists in the system or not
        $mapping_object_id_to_validate = $mappingObjectToValidate->getId();
        if ($mapping_object_id_to_validate)
        {
            // This object already exists in the system. Verify that the primary key value for $mappedReverbFieldObject
            //      matches the primary key value for the $mappingObjectToValidate passed in
            $mapped_field_object_id = $mappedReverbFieldObject->getId();
            if ($mapped_field_object_id != $mapping_object_id_to_validate)
            {
                $mapped_magento_attribute = $mappedReverbFieldObject->getMagentoAttributeCode();
                $error_message = $this->__(self::ERROR_FIELD_ALREADY_MAPPED_IN_SYSTEM, $reverb_api_field,
                                           $mapped_magento_attribute);
                throw new Exception($error_message);
            }
        }
        else
        {
            // This object does not already exist in the system. Verify that the $reverb_api_field on the object does
            //      not exist in the system
            if (is_object($mappedReverbFieldObject) && $mappedReverbFieldObject->getId())
            {
                // There is already a mapping to this reverb field in the system. Throw an exception
                $mapped_magento_attribute = $mappedReverbFieldObject->getMagentoAttributeCode();
                $error_message = $this->__(self::ERROR_FIELD_ALREADY_MAPPED_IN_SYSTEM, $reverb_api_field,
                                           $mapped_magento_attribute);
                throw new Exception($error_message);
            }
        }
    }

    public function getModuleGroupname()
    {
        return 'ReverbSync';
    }

    public function getModuleInstance()
    {
        return 'field_mapping';
    }

    public function getObjectClassname()
    {
        $objects_module_instance = $this->getModuleInstance();
        $objects_module = 'reverbSync';
        $object_classname = $objects_module . '/' . $objects_module_instance;

        return $object_classname;
    }

    public function getIndexActionsController()
    {
        return 'ReverbSync_field_mapping';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_field_mapping_index';
    }

    public function getFormBlockName()
    {
        return 'adminhtml_field_mapping';
    }

    public function getModuleInstanceDescription()
    {
        return "Magento-Reverb Field Mapping";
    }

    public function getControllerActiveMenuPath()
    {
        return 'reverb/reverb_field_sync';
    }

    public function getObjectParamName()
    {
        return 'mapping';
    }

    public function getObjectDescription()
    {
        return $this->getModuleInstanceDescription();
    }
}
