<?php

interface Reverb_Process_Model_Locked_File_Interface
    extends Reverb_Process_Model_Locked_Interface
{
    /**
     * The 2 methods below required to be implemented by child classes, no abstract class
     * implements these currently
     *
     * @return mixed
     */
    public function getLockFileDirectory();

    public function getLockFileName();
}
