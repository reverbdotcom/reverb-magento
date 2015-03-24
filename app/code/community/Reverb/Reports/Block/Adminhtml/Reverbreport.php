<?php

/**
 * Reverb Report admin block
 *
 * @category    Reverb
 * @package     Reverb_Reports
 */
class Reverb_Reports_Block_Adminhtml_Reverbreport
    extends Mage_Adminhtml_Block_Widget_Grid_Container {
    /**
     * constructor
     */
    public function __construct(){
        $this->_controller         = 'adminhtml_reverbreport';
        $this->_blockGroup         = 'reverb_reports';
        parent::__construct();
        $this->_headerText         = Mage::helper('reverb_reports')->__('Reverb Report');
        $this->_removeButton('add');
    }
}
