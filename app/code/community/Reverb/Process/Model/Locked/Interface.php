<?php

interface Reverb_Process_Model_Locked_Interface
{
    /**
     * The following are implemented by the following abstract classes:
     *      Reverb_Process_Model_Locked_File_Cron_Abstract
     *
     * In the event of database locking, these would be implemented by a class such as
     *      Reverb_Process_Model_Locked_Db_Cron_Abstract
     *
     * @return boolean
     */
    public function attemptLock();

    public function releaseLock();
}
