<?php
/**
 * Author: Sean Dunagan
 * Created: 9/14/15
 */

class Reverb_ProcessQueue_Model_Source_Task_Status
{
    protected $_options = null;

    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('reverb_process_queue')->__('Pending'),
                    'value' => Reverb_ProcessQueue_Model_Task::STATUS_PENDING
                ),
                array(
                    'label' => Mage::helper('reverb_process_queue')->__('Processing'),
                    'value' => Reverb_ProcessQueue_Model_Task::STATUS_PROCESSING
                ),
                array(
                    'label' => Mage::helper('reverb_process_queue')->__('Complete'),
                    'value' => Reverb_ProcessQueue_Model_Task::STATUS_COMPLETE
                ),
                array(
                    'label' => Mage::helper('reverb_process_queue')->__('Error'),
                    'value' => Reverb_ProcessQueue_Model_Task::STATUS_ERROR
                ),
                array(
                    'label' => Mage::helper('reverb_process_queue')->__('Failed'),
                    'value' => Reverb_ProcessQueue_Model_Task::STATUS_ABORTED
                ),
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    public function getLabelByOptionValue($option_value)
    {
        foreach ($this->getAllOptions() as $option)
        {
            $value = $option['value'];
            if($value == $option_value)
            {
                return $option['label'];
            }
        }
        return '';
    }
} 