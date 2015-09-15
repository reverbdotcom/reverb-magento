<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/IndexController.php');
class Reverb_ProcessQueue_Adminhtml_Unique_IndexController
    extends Reverb_ProcessQueue_Adminhtml_IndexController
    implements Reverb_Base_Controller_Adminhtml_Form_Interface
{
    public function getControllerActiveMenuPath()
    {
        return 'system/reverb_process_queue_unique';
    }

    public function getModuleInstanceDescription()
    {
        return 'Unique Process Queue Tasks';
    }

    public function getIndexBlockName()
    {
        return 'adminhtml_task_unique_index';
    }

    public function getObjectParamName()
    {
        return 'unique_task';
    }

    public function getObjectDescription()
    {
        return 'Unique Task';
    }

    public function getModuleInstance()
    {
        return 'task_unique';
    }

    public function getFormBlockName()
    {
        return 'adminhtml_task_unique';
    }

    public function getFormActionsController()
    {
        return 'adminhtml_unique_index';
    }

    public function getFormBackControllerActionPath()
    {
        return 'adminhtml_unique_index/index';
    }
}
