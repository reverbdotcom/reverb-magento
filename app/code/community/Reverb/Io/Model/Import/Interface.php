<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 * Interface Reverb_Io_Model_Import_Interface
 */
interface Reverb_Io_Model_Import_Interface extends Reverb_Io_Model_Process_Interface
{
    // The following methods are REQUIRED for classes which implement this interface
    /**
     * @param Reverb_Io_Model_Io_File $ioAdapter - Adapter executing IO functionality
     * @param array $file - array containing data regarding the file to be read
     * @param string $file_path - the file's absolute filepath
     * @return mixed
     */
    public function readFile($ioAdapter, $file, $file_path);

    // The following methods are OPTIONAL to override for subclasses of abstract class Reverb_Io_Model_Import_Abstract

    /**
     * The directory to import files from
     *
     * @return string - Default:  '{magento_root}/var/import'
     */
    public function getImportDirectory();

    /**
     * Will return if file is valid to be processed
     *
     * @param $filename
     * @return bool - If the file is valid to be processed. By default, ensures that the file is not an object file (ending in ~)
     */
    public function validateFilename($filename);

    /**
     * In the event that an import has multiple phases, this method should be called between phases
     *
     * @return mixed
     */
    public function resetForNextReadPhase();

    // The following instance fields are OPTIONAL to define for subclasses of abstract class Reverb_Io_Model_Import_Abstract
    /**
     * This field determines whether an error should be logged when a file with an invalid name is found in the import directory
           protected $_suppress_invalid_filename_errors = false;
     */
}
