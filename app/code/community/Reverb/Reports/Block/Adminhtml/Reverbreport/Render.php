<?php
class Reverb_Reports_Block_Adminhtml_Reverbreport_Render
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {
    $name = $row -> getData($this -> getColumn() -> getIndex());
    $id = $row -> getData($this -> getColumn() -> getData('product_id'));
    $url = Mage::helper('adminhtml') -> getUrl('adminhtml/catalog_product/edit', array('id' => $id));
    return '<a href="' . $url . '">' . $name . '</a>';

  }

}
?>   