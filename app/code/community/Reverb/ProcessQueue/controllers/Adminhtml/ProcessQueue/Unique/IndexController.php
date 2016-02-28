<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */

require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/IndexController.php');
class Reverb_ProcessQueue_Adminhtml_ProcessQueue_Unique_IndexController
  extends Reverb_ProcessQueue_Adminhtml_ProcessQueue_IndexController
  implements Reverb_Base_Controller_Adminhtml_Form_Interface
{
    protected function _getTaskProcessor()
    {
      return Mage::helper('reverb_process_queue/task_processor_unique');
    }

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
      return 'task_unique';
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

    public function getIndexActionsController()
    {
      return 'ProcessQueue_unique_index';
    }
}
