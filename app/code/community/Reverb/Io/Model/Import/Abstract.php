<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 *
 * Class Reverb_Io_Model_Import_Abstract
 */
abstract class Reverb_Io_Model_Import_Abstract
    extends Reverb_Io_Model_Process_Abstract
    implements Reverb_Io_Model_Import_Interface
{
    const DEFAULT_IMPORT_FILE_PERMISSIONS = 0777;
    const ERR_FILE_LOCK = 'Unable to obtain lock for import file %s';
    const ERR_INVALID_FILENAME = 'Found file with invalid filename "%s". This file will not be processed.';
    const OBJECT_FILE_REGEX = '#.*~$#';

    protected $_error_log_file = 'io_import_error';
    protected $_suppress_invalid_filename_errors = false;

    protected $_row_number = 0;
    protected $_current_file_being_processed = '';

    abstract public function readFile($ioAdapter, $file, $file_path);

    public function run()
    {
        try
        {
            $this->read();
        }
        catch (Exception $e)
        {
            $this->logError($e->getMessage());
        }
    }

    public function read()
    {
        $ioAdapter = $this->_getIoAdapter();
        $entityImportDir = $this->getImportDirectory();
        $ioAdapter->open(array('path' => $entityImportDir));
        $files = $ioAdapter->ls(Varien_Io_File::GREP_FILES);

        foreach($files as $file)
        {
            if(!$this->validateFilename($file['text']))
            {
                if (!$this->_suppress_invalid_filename_errors)
                {
                    $this->logError(sprintf(self::ERR_INVALID_FILENAME, $file['text']));
                }
                // Move on to the next file in the import directory
                continue;
            }

            $filePath = $entityImportDir. DS . $file['text'];
            $ioAdapter->streamOpen($filePath, 'r', self::DEFAULT_IMPORT_FILE_PERMISSIONS);
            if(!$ioAdapter->streamLock(true))
            {
                throw new Reverb_Io_Model_Exception(sprintf(self::ERR_FILE_LOCK, $filePath));
            }

            $this->_current_file_being_processed = $file['text'];
            $this->readFile($ioAdapter, $file, $filePath);
            $this->_current_file_being_processed = null;

            $ioAdapter->streamUnlock();
            $ioAdapter->streamClose();
        }
    }

    public function resetForNextReadPhase()
    {
        $this->_current_file_being_processed = null;
    }

    public function getImportDirectory()
    {
        return parent::getTransactionDirectory() . DS . "import";
    }

    /*
     * Ensure that this file is not an object file (denoted by having a ~ at the end of the file)
     */
    public function validateFilename($filename)
    {
        return (!preg_match(self::OBJECT_FILE_REGEX, $filename));
    }

    protected function _getCurrentRowNumber()
    {
        return $this->_row_number;
    }
}
