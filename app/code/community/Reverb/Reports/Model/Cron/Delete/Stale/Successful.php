<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 12/9/15
 */

class Reverb_Reports_Model_Cron_Delete_Stale_Successful
{
    const CRON_UNCAUGHT_EXCEPTION = 'Error deleting stale success reports from the Reverb Reports Table: %s';

    public function deleteStaleSuccessfulReports()
    {
        try
        {
            Mage::getResourceSingleton('reverb_reports/Reverbreport')->deleteStaleSuccessfulReports();
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::CRON_UNCAUGHT_EXCEPTION, $e->getMessage());
            Mage::log($error_message, null, 'reverb_process_queue_error.log');
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);
        }
    }
}
