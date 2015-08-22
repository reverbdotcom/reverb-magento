<?php

interface Reverb_Process_Model_Locked_File_Cron_Interface
    extends Reverb_Process_Model_Locked_File_Interface, Reverb_Process_Model_Locked_Cron_Interface
{
    public function getLockFileDirectory();

    public function getLockFileName();
}
