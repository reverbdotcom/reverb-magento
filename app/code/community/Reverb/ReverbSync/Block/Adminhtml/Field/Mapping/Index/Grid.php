<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 4/18/16
 */

class Reverb_ReverbSync_Block_Adminhtml_Field_Mapping_Index_Grid extends Reverb_Base_Block_Adminhtml_Widget_Grid
{
    protected function _prepareColumns()
    {
        $this->addColumn(
            'reverb_api_field',
            array(
                'header' => $this->_getTranslationHelper()->__('Reverb API Field'),
                'align'  => 'left',
                'index'  => Reverb_ReverbSync_Model_Field_Mapping::REVERB_API_FIELD_FIELD,
                'type'   => 'text'
            )
        );

        $this->addColumn(
            'magento_attribute_code',
            array(
                'header' => $this->_getTranslationHelper()->__('Magento Attribute Code'),
                'align'  => 'left',
                'index'  => Reverb_ReverbSync_Model_Field_Mapping::MAGENTO_ATTRIBUTE_FIELD,
                'type'   => 'text'
            )
        );

        return $this;
    }
}
