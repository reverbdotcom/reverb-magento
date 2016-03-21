<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 * Class Reverb_Io_Model_Process_Abstract
 */
abstract class Reverb_Io_Model_Process_Abstract
    extends Varien_Object
    implements Reverb_Io_Model_Process_Interface
{
    protected $_error_log_file = 'io_process_error';
    protected $_error_log_file_extension = '.log';

    protected function _getIoAdapter()
    {
        if (!$this->hasData('io_adapter'))
        {
            $ioAdapter = Mage::getModel('reverb_io/io_file');
            $ioAdapter->setAllowCreateFolders(true);
            $this->setData('io_adapter', $ioAdapter);
        }

        return $this->getData('io_adapter');
    }

    public function getTransactionDirectory()
    {
        return Mage::getBaseDir('var');
    }

    public function logError($message)
    {
        $filename = $this->_error_log_file . $this->_error_log_file_extension;

        Mage::log($message, Zend_Log::ERR, $filename);

        return $this;
    }

    public function logMessage($message, $message_level = null)
    {
        $filename = $this->_error_log_file . $this->_error_log_file_extension;

        $message_level = is_null($message_level) ? Zend_Log::INFO : $message_level;

        Mage::log($message, $message_level, $filename);

        return $this;
    }
}