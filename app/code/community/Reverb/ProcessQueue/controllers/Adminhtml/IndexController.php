<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */


class Reverb_ProcessQueue_Adminhtml_IndexController
    extends Reverb_Base_Controller_Adminhtml_Form_Abstract
    implements Reverb_Base_Controller_Adminhtml_Form_Interface
{
    /**
     * Allow Queue Tasks to be created via these forms
     *
     * @param $objectToCreate
     * @param $posted_object_data
     * @return mixed
     */
    public function validateDataAndCreateObject($objectToCreate, $posted_object_data)
    {
        $objectToCreate->setLastExecutedAt(null);
        return $objectToCreate->addData($posted_object_data);
    }

    public function validateDataAndUpdateObject($objectToUpdate, $posted_object_data)
    {
        // Only the status field should have been passed
        $new_status = isset($posted_object_data['status']) ? $posted_object_data['status'] : null;
        if (!is_null($new_status))
        {
            $objectToUpdate->setStatus($new_status);
        }

        return $objectToUpdate;
    }

    public function getModuleGroupname()
    {
        return 'reverb_process_queue';
    }

    public function getControllerActiveMenuPath()
    {
        return 'system/reverb_process_queue';
    }

    public function getModuleInstanceDescription()
    {
        return 'Process Queue Tasks';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_task_index';
    }

    public function getObjectParamName()
    {
        return 'task';
    }

    public function getObjectDescription()
    {
        return 'Task';
    }

    public function getModuleInstance()
    {
        return 'task';
    }

    public function getFormBlockName()
    {
        return 'adminhtml_task';
    }

    public function getFormActionsController()
    {
        return 'adminhtml_index';
    }

    public function getFormBackControllerActionPath()
    {
        return 'adminhtml_index/index';
    }
}
