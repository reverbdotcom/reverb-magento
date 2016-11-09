<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 12/9/15
 */

class Reverb_ProcessQueue_Model_Cron_Delete_Stale_Successful
{
    const CRON_UNCAUGHT_EXCEPTION = 'Error deleting stale success tasks from the Reverb Process Queue: %s';

    /**
     * It's not optimal that the configuration setting for this Reverb_ProcessQueue module class is in the ReverbSync
     *  module's configuration setting, but creating a Reverb_ProcessQueue module-specific system.xml tab and
     *  section seemed like sub-optimal UX. As such, this setting is in the already-existing ReverbSync module's
     *  system.xml structure.
     */
    const STALE_TASK_DELETION_ENABLED_CONFIG_PATH = 'ReverbSync/stale_task_deletion/enabled';

    /**
     * @var null|bool
     */
    protected $_is_stale_task_deletion_enabled = null;

    /**
     * A nightly cronjob to delete tasks from the system which are "stale", meaning have been completed for a period of
     *  time defined in the admin panel
     */
    public function deleteStaleSuccessfulQueueTasks()
    {
        try
        {
            // See if the nightly cronjob to delete stale successful tasks has been enabled
            if ($this->_isStaleTaskDeletionEnabled())
            {
                $taskHelper = Mage::helper('reverb_process_queue/task');
                /* @var Reverb_ProcessQueue_Helper_Task $taskHelper */
                $taskHelper->deleteStaleSuccessfulTasks();
            }
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::CRON_UNCAUGHT_EXCEPTION, $e->getMessage());
            Mage::log($error_message, null, 'reverb_process_queue_error.log');
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);
        }
    }

    /**
     * @return bool
     */
    protected function _isStaleTaskDeletionEnabled()
    {
        if (is_null($this->_is_stale_task_deletion_enabled))
        {
            $stale_task_deletion_is_enabled = Mage::getStoreConfig(self::STALE_TASK_DELETION_ENABLED_CONFIG_PATH);
            $this->_is_stale_task_deletion_enabled = boolval($stale_task_deletion_is_enabled);
        }

        return $this->_is_stale_task_deletion_enabled;
    }
}
