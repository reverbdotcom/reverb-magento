<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Index_Grid
    extends Reverb_Base_Block_Adminhtml_Widget_Grid
{
    protected function _prepareColumns()
    {
        $this->addColumn('reverb_order_id', array(
            'header'    => $this->_getTranslationHelper()->__('Reverb Order ID'),
            'width'     => 50,
            'align'     => 'left',
            'type'      => 'text',
            'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_order_reverb_id',
            'filter'    => false,
            'sortable'  => false
        ));

        $this->addColumn('status', array(
            'header'    => $this->_getTranslationHelper()->__('Status'),
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getModel('reverb_process_queue/source_task_status')->getOptionArray()
        ));

        $this->addColumn('sku', array(
            'header'    => $this->_getTranslationHelper()->__('Sku'),
            'align'     => 'left',
            'type'      => 'text',
            'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_order_product_sku',
            'filter'    => false,
            'sortable'  => false
        ));

        $this->addColumn('name', array(
            'header'    => $this->_getTranslationHelper()->__('Name'),
            'align'     => 'left',
            'type'      => 'text',
            'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_order_product_name',
            'filter'    => false,
            'sortable'  => false
        ));

        $this->addColumn('status_message', array(
            'header'    => $this->_getTranslationHelper()->__('Status Message'),
            'align'     => 'left',
            'index'     => 'status_message',
            'type'      => 'text'
        ));

        $this->addColumn('created_at', array(
            'header'    => $this->_getTranslationHelper()->__('Created At'),
            'align'     => 'left',
            'index'     => 'created_at',
            'type'      => 'datetime'
        ));

        $this->addColumn('last_executed_at', array(
            'header'    => $this->_getTranslationHelper()->__('Last Executed At'),
            'align'     => 'left',
            'index'     => 'last_executed_at',
            'type'      => 'datetime'
        ));

        $this->addColumn('action', array(
            'header'    => $this->_getTranslationHelper()->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'    => 'getId',
            'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_order_task_action',
            'filter'    => false,
            'sortable'  => false,
            'task_controller' => 'ReverbSync_orders_sync'
        ));

        return parent::_prepareColumns();
    }

    public function setCollection($collection)
    {
        $collection->addCodeFilter('order_update');
        parent::setCollection($collection);
    }
}
