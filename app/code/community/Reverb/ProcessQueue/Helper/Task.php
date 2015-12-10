<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 12/9/15
 */

class Reverb_ProcessQueue_Helper_Task extends Mage_Core_Helper_Data
{
    const STALE_TIME_LAPSE = '-2 weeks';

    /**
     * The calling block is expected to catch exceptions
     *
     * @param null|string $task_code
     * @return int - Number of rows deleted
     */
    public function deleteStaleSuccessfulTasks($task_code = null)
    {
        $current_gmt_timestamp = Mage::getSingleton('core/date')->gmtTimestamp();
        $stale_timestamp = strtotime(self::STALE_TIME_LAPSE, $current_gmt_timestamp);
        $stale_date = date('Y-m-d H:i:s', $stale_timestamp);

        $rows_deleted = Mage::getResourceSingleton('reverb_process_queue/task')
                            ->deleteSuccessfulTasks($task_code, $stale_date);
        $unique_rows_deleted = Mage::getResourceSingleton('reverb_process_queue/task_unique')
                                ->deleteSuccessfulTasks($task_code, $stale_date);

        return ($rows_deleted + $unique_rows_deleted);
    }
}
