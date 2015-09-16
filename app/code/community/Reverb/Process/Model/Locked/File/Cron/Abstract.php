<?php

abstract class Reverb_Process_Model_Locked_File_Cron_Abstract
    extends Reverb_Process_Model_Locked_Cron_Abstract
    implements Reverb_Process_Model_Locked_File_Cron_Interface
{
    const ERROR_EXCEPTION_WHILE_OPENING_LOCK_FILE = 'Error attempting to open the lock file %s: %s';
    const ERROR_EXCEPTION_WHILE_SECURING_LOCK_FILE = 'Error attempting to secure the lock file %s: %s';
    const ERROR_EXCEPTION_WHILE_CHANGING_DIRECTORY = 'Error attempting to change to directory %s to lock file %s: %s';
    const ERROR_EXCEPTION_CHECKING_AND_CREATING_FOLDER = 'Exception occurred while checking existence of directory %s: %s';

    const LOCK_FILE_PERMISSIONS = 0700;

    protected $_ioAdapter = null;

    abstract public function getLockFileDirectory();

    abstract public function getLockFileName();

    public function attemptLockForThread($thread_number)
    {
        $ioAdapter = $this->_getIoAdapter();
        $lock_file_directory = $this->getLockFileDirectory();
        $lock_file_name = $this->getLockFileName();
        $full_lock_file_name = $lock_file_name . '_' . $thread_number . '.lock';

        try
        {
            $ioAdapter->open(array('path' => $lock_file_directory));
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::ERROR_EXCEPTION_WHILE_CHANGING_DIRECTORY, $lock_file_directory, $full_lock_file_name, $e->getMessage());
            $this->_logError($error_message);
            return false;
        }

        $lock_file_path = $lock_file_directory . DS . $full_lock_file_name;

        try
        {
            $ioAdapter->streamOpen($lock_file_path, 'w+', self::LOCK_FILE_PERMISSIONS);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::ERROR_EXCEPTION_WHILE_OPENING_LOCK_FILE, $lock_file_path, $e->getMessage());
            $this->_logError($error_message);
            return false;
        }

        try
        {
            if(!$ioAdapter->streamLock(true))
            {
                return false;
            }
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::ERROR_EXCEPTION_WHILE_SECURING_LOCK_FILE, $lock_file_path, $e->getMessage());
            $this->_logError($error_message);

            return false;
        }

        return true;
    }

    public function releaseLock()
    {
        $ioAdapter = $this->_getIoAdapter();

        try
        {
            $ioAdapter->streamUnlock();
            $ioAdapter->streamClose();
        }
        catch(Exception $e)
        {
            return false;
        }

        return true;
    }

    protected function _getIoAdapter()
    {
        if (is_null($this->_ioAdapter))
        {
            $this->_ioAdapter = Mage::getModel('reverb_io/io_file')->setAllowCreateFolders(true);
        }

        return $this->_ioAdapter;
    }
}
