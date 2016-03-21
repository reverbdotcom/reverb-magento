<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/21/16
 * Interface Reverb_Io_Model_Import_Csv_Interface
 */
interface Reverb_Io_Model_Import_Csv_Interface extends Reverb_Io_Model_Import_Interface
{
    // The following methods are REQUIRED for classes which implement this interface
    /**
     * The header row which must be set on a file being imported in order to be considered valid to process
     *
     * @return array - array containing the fields which should be included in the header
     */
    public function getRequiredHeaders();

    /**
     * Defines what functionality needs to occur in order to import the row's data into the system
     * THIS METHOD SHOULD NOT THROW ANY UNCAUGHT EXCEPTIONS
     *
     * @param Varien_Object $rowData - contains the header row fields mapped to the row's fields of data
     * @param int $row_num -the row number of the file, can be used for error messaging
     */
    public function importDataRow($rowData, $row_num);

    // The following methods are OPTIONAL to override for subclasses of abstract class Reverb_Io_Model_Import_Csv_Abstract

    /**
     * Allows for determining if the row is valid to be processed based on the data contained in the row
     * It is expected that this method will throw an exception upon finding a validation error, which will be caught
     * by Reverb_Io_Model_Import_Csv_Abstract::_processRow()
     *
     * @param Varien_Object $rowData - contains the header row fields mapped to the row's fields of data
     * @param int $row_num - the row number of the file, can be used for error messaging
     * @return bool - Bool determining whether the row is valid to be processed for import
     */
    public function isDataValid($rowData, $row_num);

    /**
     * @param array $headerRow - First row of csv import file
     * @param $fileName - name of the csv file
     * @return mixed - By default, will ensure that the header row matches the array returned by getRequiredHeaders();
     */
    public function validateHeader(array $headerRow, $fileName);

    /**
     * Allows for defining functionality which should occur if an import file contains an invalid row
     * By default Reverb_Io_Model_Import_Csv_Abstract takes no action
     *
     * @param array $row - Array containing the fields contained in the row
     */
    public function actOnInvalidRow($row);

    /**
     * Allows for defining functionality which should occur if an import file has an invalid header row
     * By default Reverb_Io_Model_Import_Csv_Abstract takes no action
     *
     * @param $fileName - Name of the file which has an invalid header
     * @param array $headerRow - array containing the fields in the header row
     */
    public function actOnInvalidHeaderFile($fileName, $headerRow);

    /**
     * In the event that an import has multiple phases, this method should be called between phases
     *
     * @return mixed
     */
    public function resetForNextReadPhase();

    // The following instance fields are OPTIONAL to define for subclasses of abstract class Reverb_Io_Model_Import_Csv_Abstract
    /**
     * The following define the delimiter and enclosure to be used for calls to fgetcsv()
     *      protected $_file_delimiter = ',';
     *      protected $_file_enclosure = '"';
     *
     * The following defines whether errors should be logged when a row's data is found to be invalid. This does NOT
     *  include issues with rows containing the wrong number of fields
     *      protected $_suppress_row_validation_errors = false;
     */
}
