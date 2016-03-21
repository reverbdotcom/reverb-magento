<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 *
 * Interface Reverb_Io_Model_Process_Interface
 */
interface Reverb_Io_Model_Process_Interface
{
    // The following methods are OPTIONAL to override for subclasses of abstract class Reverb_Io_Model_Process_Abstract
    /**
     * Directory to import files from or export files to
     *
     * @return string - Default: Mage::getBaseDir('var');
     */
    public function getTransactionDirectory();

    // The following instance fields are OPTIONAL to be defined by subclasses of abstract class Reverb_Io_Model_Process_Abstract
    /*
     * The following two instance fields determine the name of the file which will log all errors/messages for the process
        protected $_error_log_file = 'io_process_error';
        protected $_error_log_file_extension = '.log';
     */
}
