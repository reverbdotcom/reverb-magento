<?php
/**
 * Author: Sean Dunagan
 * Created: 8/13/15
 * Class Reverb_ProcessQueue_Model_Mysql_Task_Collection
 */

class Reverb_ProcessQueue_Model_Mysql4_Task_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    const DEFAULT_MINUTES_IN_PAST_THRESHOLD = 120;

    protected function _construct()
    {
        $this->_init('reverb_process_queue/task');
    }

    public function addOpenForProcessingFilter()
    {
        $pending_status = Reverb_ProcessQueue_Model_Task::STATUS_PENDING;
        $error_status = Reverb_ProcessQueue_Model_Task::STATUS_ERROR;
        $open_for_processing_states = array($pending_status, $error_status);

        $this->addFieldToFilter('status', array('in' => $open_for_processing_states));
        return $this;
    }

    public function addLastExecutedAtThreshold($minutes_in_past = self::DEFAULT_MINUTES_IN_PAST_THRESHOLD)
    {
        $current_gmt_timestamp = Mage::getSingleton('core/date')->gmtTimestamp();
        $second_in_past = $minutes_in_past * 60;
        $last_executed_at_threshold = $current_gmt_timestamp - $second_in_past;

        $this->addFieldToFilter('last_executed_at', array('lt' => $last_executed_at_threshold));
        return $this;
    }

    public function addCodeFilter($code)
    {
        if (is_array($code))
        {
            $code = array('in' => $code);
        }
        $this->addFieldToFilter('code', $code);
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }

    public function sortByLeastRecentlyExecuted()
    {
        $this->getSelect()->order('last_executed_at ' . Zend_Db_Select::SQL_ASC);
        return $this;
    }
}
