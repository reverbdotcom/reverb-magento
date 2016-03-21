<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 * Class Reverb_Io_Model_Import_Csv_Abstract
 */
abstract class Reverb_Io_Model_Import_Csv_Abstract
    extends Reverb_Io_Model_Import_Abstract
    implements Reverb_Io_Model_Import_Csv_Interface
{
    const FILE_HEADER_INVALID_SIZE = 'The header row of file %s is expected to have %s fields, but instead has %s fields. This file will not be processed.';
    const ERR_MISSING_REQUIRED_COLUMN = 'File "%s" is missing a required header column %s.  This file will be processed.';
    const ROW_HAS_WRONG_NUMBER_OF_FIELDS = 'Row %s of file %s is expected to contain %s fields, but instead contains %s fields. This row will not be imported.';
    const ERR_DATA_ROW_IS_INVALID = 'Row %s of file "%s" is invalid and will be skipped due to the following error: %s';
    const FILE_ONLY_HAS_HEADER_ROW = 'File "%s" only contains a header row. No data was processed from this file.';
    const ERROR_READING_ENTIRE_FILE = 'Unable to reach the end of file "%s". Entire file was not read.';

    abstract public function getRequiredHeaders();
    abstract public function importDataRow($rowData, $row_num);

    protected $_file_delimiter = ',';
    protected $_file_enclosure = '"';
    protected $_suppress_row_validation_errors = false;

    protected $_expected_row_size = null;
    protected $_array_to_use_for_data_array_preparation = null;

    public function readFile($ioAdapter, $file, $file_path)
    {
        $this->_current_file_being_processed = $file['text'];
        $this->_row_number = 0;

        // Build array, cells with header names.
        $header_row = $this->_getHeaderRow($ioAdapter);

        $this->_header_row = null;

        if (!$this->validateHeader($header_row, $file['text']))
        {
            // Reset this->_header_row in case a subclass has set it
            // Method _validateHeader() is expected to have logged any necessary error messaging
            $this->_header_row = null;
            return false;
        }

        $this->_header_row = $header_row;

        while(false !== ($row = $this->_getNextRow($ioAdapter)))
        {
            if (!$this->_validateRowSize($row))
            {
                $row_columns = count($row);
                $row_number = $this->_getCurrentRowNumber();
                $error_message = sprintf(self::ROW_HAS_WRONG_NUMBER_OF_FIELDS, $row_number, $file['text'], $this->_getExpectedRowSize(), $row_columns);
                $this->logError($error_message);
                $this->actOnInvalidRow($row);
                // Don't process this row
                continue;
            }

            $toProcess = $this->_prepareForProcessing($header_row, $row);
            $this->_processRow($this->_row_number, $toProcess, $file['text']);
            $row = false;
        }

        // Does this file have any data?
        if($this->_row_number == 1)
        {
            $this->logError(
                sprintf(self::FILE_ONLY_HAS_HEADER_ROW, $file['text'])
            );
        }

        // Check if we reached EOF.  If not, something went wrong.
        if(!feof($ioAdapter->getStream()))
        {
            throw new Reverb_Io_Model_Exception(sprintf(self::ERROR_READING_ENTIRE_FILE, $file['text']));
        }
    }

    protected function _processRow($row_num, $rowData, $fileName)
    {
        try
        {
            if (!$this->isDataValid($rowData, $row_num))
            {
                $error_message = sprintf('Function isDataValid() returned false for row %s of file %s', $row_num, $fileName);
                throw new Reverb_Io_Model_Exception($error_message);
            }
        }
        catch(Exception $e)
        {
            if (!$this->_suppress_row_validation_errors)
            {
                $this->logError(sprintf(self::ERR_DATA_ROW_IS_INVALID, $row_num, $fileName, $e->getMessage()));
            }

            return false;
        }

        $this->importDataRow($rowData, $row_num);
    }

    public function validateHeader(array $header_row, $fileName)
    {
        $is_header_valid = true;

        if (!$this->_validateRowSize($header_row))
        {
            $expected_row_size = $this->_getExpectedRowSize();
            $actual_row_size = count($header_row);
            $this->logError(
                sprintf(self::FILE_HEADER_INVALID_SIZE, $fileName, $expected_row_size, $actual_row_size)
            );
            $is_header_valid = false;
        }
        else
        {
            $required_headers = $this->getRequiredHeaders();

            foreach($required_headers as $required_header)
            {
                $array_search_value = array_search($required_header, $header_row);
                if($array_search_value === FALSE)
                {
                    $this->logError(
                        sprintf(self::ERR_MISSING_REQUIRED_COLUMN, $fileName, $required_header)
                    );
                    $is_header_valid = false;
                }
            }
        }

        if (!$is_header_valid)
        {
            $this->actOnInvalidHeaderFile($fileName, $header_row);
        }

        return $is_header_valid;
    }

    /**
     * @param Reverb_Io_Model_Io_File $ioAdapter
     * @return string
     */
    protected function _getHeaderRow($ioAdapter)
    {
        return $this->_getNextRow($ioAdapter);
    }

    /**
     * @param Reverb_Io_Model_Io_File $ioAdapter
     * @return string
     */
    protected function _getNextRow($ioAdapter)
    {
        $this->_row_number++;
        return $ioAdapter->streamReadCsv($this->_file_delimiter, $this->_file_enclosure);
    }

    protected function _validateRowSize(array $row)
    {
        $expected_row_size = $this->_getExpectedRowSize();
        $actual_row_size = count($row);

        return ($expected_row_size == $actual_row_size);
    }

    protected function _getExpectedRowSize()
    {
        if (is_null($this->_expected_row_size))
        {
            $this->_expected_row_size = count($this->getRequiredHeaders());
        }

        return $this->_expected_row_size;
    }

    protected function _prepareForProcessing($header_row, $row)
    {
        return new Varien_Object(array_combine($this->_getArrayToUseForDataPreparation($header_row), $row));
    }

    protected function _getArrayToUseForDataPreparation($header_row)
    {
        if (is_null($this->_array_to_use_for_data_array_preparation))
        {
            $this->_array_to_use_for_data_array_preparation = $this->getRowDataObjectKeysArray($header_row);
        }

        return $this->_array_to_use_for_data_array_preparation;
    }

    public function getRowDataObjectKeysArray($header_row)
    {
        return $header_row;
    }

    public function resetForNextReadPhase()
    {
        parent::resetForNextReadPhase();

        $this->_expected_row_size = null;
        $this->_header_row = null;
    }

    /*
     * The following methods may be overridden by subclasses if necessary
     */
    public function actOnInvalidHeaderFile($fileName, $header_row)
    {
        return true;
    }

    public function actOnInvalidRow($row)
    {
        return true;
    }

    public function isDataValid($rowData, $row_num)
    {
        return true;
    }
}