<?php

class Reverb_Reports_Block_Adminhtml_Reverbreport_Grid
    extends Mage_Adminhtml_Block_Widget_Grid {
   
    public function __construct(){
        parent::__construct();
        $this->setId('reverbreportGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    /**
     * prepare collection
     * @access protected
     * @return Reverb_Reports_Block_Adminhtml_Reverbreport_Grid
    
     */
    protected function _prepareCollection(){
        $collection = Mage::getModel('reverb_reports/reverbreport')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
   
    protected function _prepareColumns(){
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('reverb_reports')->__('Id'),
            'index'        => 'entity_id',
            'type'        => 'number',
            'filter' => false,
        ));
         $this->addColumn('title', array(
            'header'    => Mage::helper('reverb_reports')->__('Title'),
            'align'     => 'left',
            'index'=>'title',
            'product_id' => 'product_id',
             'type'=> 'text',
             'renderer' =>  'Reverb_Reports_Block_Adminhtml_Reverbreport_Render',                                        
        ));
        
         $this->addColumn('product_sku', array(
            'header'=> Mage::helper('reverb_reports')->__('SKU'),
            'index' => 'product_sku',
            'type'=> 'text',

        ));
         $this->addColumn('inventory', array(
            'header'=> Mage::helper('reverb_reports')->__('Inventory'),
            'index' => 'inventory',
            'type'=> 'number',
           'filter' => false,
        ));
        
         $this->addColumn('rev_url', array(
            'header'=> Mage::helper('reverb_reports')->__('Reverb URL'),
            'index' => 'rev_url',
            'type'=> 'text',
            'filter' => false,
            'renderer' =>  'Reverb_Reports_Block_Adminhtml_Reverbreport_Render',
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('reverb_reports')->__('Sync Status'),
            'index'        => 'status',
            'type'        => 'options',
            'options'    => array(
                '1' => Mage::helper('reverb_reports')->__('success'),
                '0' => Mage::helper('reverb_reports')->__('failed_reverb_sync'),
            )
        ));
        
    
        $this->addColumn('sync_details', array(
            'header'=> Mage::helper('reverb_reports')->__('Sync Details'),
            'index' => 'sync_details',
            'type'=> 'text',
           'filter' => false,
        ));
       
        $this->addColumn('last_synced', array(
            'header'    => Mage::helper('reverb_reports')->__('Last Synced'),
            'index'     => 'last_synced',
            'width'     => '120px',
            'type'      => 'datetime',
            'filter' => false,
        ));
        
        
    }
    
   
    public function getGridUrl(){
        return $this->getUrl('*/*/ajaxGrid', array('_current'=>true));
  }
    

}
