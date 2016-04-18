<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/18/16
 */

class Reverb_ReverbSync_Adminhtml_ReverbSync_Field_MappingController
    extends Reverb_Base_Controller_Adminhtml_Form_Abstract
    implements Reverb_Base_Controller_Adminhtml_Form_Interface
{
    public function validateDataAndUpdateObject($objectToUpdate, $posted_object_data)
    {
        $objectToUpdate->addData($posted_object_data);
        return $objectToUpdate;
    }

    public function validateDataAndCreateObject($objectToCreate, $posted_object_data)
    {
        $objectToCreate->setData($posted_object_data);
        return $objectToCreate;
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
