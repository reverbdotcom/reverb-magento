<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 12/9/15
 */

class Reverb_ProcessQueue_Helper_Task extends Mage_Core_Helper_Data
{
    /**
     * It's not optimal that the configuration setting for this Reverb_ProcessQueue module class is in the ReverbSync
     *  module's configuration setting, but creating a Reverb_ProcessQueue module-specific system.xml tab and
     *  section seemed like sub-optimal UX. As such, this setting is in the already-existing ReverbSync module's
     *  system.xml structure.
     */
    const STALE_TASK_TIME_LAPSE_CONFIG_PATH = 'ReverbSync/stale_task_deletion/stale_period_in_days';
    const STALE_TASK_TIME_LAPSE_SPRINTF_TEMPLATE = '-%s days';

    /**
     * The calling block is expected to catch exceptions
     *
     * @param null|string $task_code
     * @return int - Number of rows deleted
     */
    public function deleteStaleSuccessfulTasks($task_code = null)
    {
        $current_gmt_timestamp = Mage::getSingleton('core/date')->gmtTimestamp();
        $stale_task_time_lapse_strtotime_param = $this->_getStaleTaskTimeLapseInDaysStrToTimeParam();
        $stale_timestamp = strtotime($stale_task_time_lapse_strtotime_param, $current_gmt_timestamp);
        $stale_date = date('Y-m-d H:i:s', $stale_timestamp);
        // Flush the task table
        $taskResourceSingleton = Mage::getResourceSingleton('reverb_process_queue/task');
        /* @var Reverb_ProcessQueue_Model_Mysql4_Task $taskResourceSingleton */
        $rows_deleted = $taskResourceSingleton->deleteSuccessfulTasks($task_code, $stale_date);
        // Flush the unique task table
        $uniqueTaskResourceSingleton = Mage::getResourceSingleton('reverb_process_queue/task_unique');
        /* @var Reverb_ProcessQueue_Model_Mysql4_Task_Unique $uniqueTaskResourceSingleton */
        $unique_rows_deleted = $uniqueTaskResourceSingleton->deleteSuccessfulTasks($task_code, $stale_date);

        return ($rows_deleted + $unique_rows_deleted);
    }

    /**
     * Returns the first parameter for strtotime signifying the time in days that tasks should be completed for
     *  in order to be considered stale
     *
     * @return string
     */
    protected function _getStaleTaskTimeLapseInDaysStrToTimeParam()
    {
        $stale_task_time_lapse_in_days = Mage::getStoreConfig(self::STALE_TASK_TIME_LAPSE_CONFIG_PATH);
        $stale_task_time_lapse_in_days = intval($stale_task_time_lapse_in_days);
        $stale_task_time_lapse_strtotime_param
            = sprintf(self::STALE_TASK_TIME_LAPSE_SPRINTF_TEMPLATE, $stale_task_time_lapse_in_days);

        return $stale_task_time_lapse_strtotime_param;
    }
}
