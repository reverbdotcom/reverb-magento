<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 2/4/16
 */

class Reverb_Base_Helper_Product extends Mage_Core_Helper_Abstract
{
    const ERROR_PRODUCT_NOT_CONFIGURABLE = 'Product with sku %s is not a configurable product';

    protected $_configurableProductTypeModel = null;

    /**
     * @param $product
     * @return Mage_Catalog_Model_Product|null
     */
    public function getParentProductIfChild($product)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            return null;
        }
        $parent_id_array = $this->_getConfigurableProductTypeModel()->getParentIdsByChild($product->getId());
        if (!empty($parent_id_array))
        {
            $parent_id = reset($parent_id_array);
            $parentProduct = Mage::getModel('catalog/product')->load($parent_id);
            return $parentProduct;
        }

        return null;
    }

    /**
     * @param Mage_Catalog_Model_Product $configurableProduct
     * @return array
     * @throws Exception
     */
    public function getSimpleProductsForConfigurableProduct($configurableProduct)
    {
        if ($configurableProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
        {
            $error_message = $this->__(self::ERROR_PRODUCT_NOT_CONFIGURABLE, $configurableProduct->getSku());
            throw new Exception($error_message);
        }

        $parent_id = $configurableProduct->getId();
        $child_product_ids_return = $this->_getConfigurableProductTypeModel()->getChildrenIds($parent_id);
        $child_product_ids = reset($child_product_ids_return);
        $child_products_array = array();
        foreach($child_product_ids as $child_product_id)
        {
            $childProduct = Mage::getModel('catalog/product')->load($child_product_id);
            if (is_object($childProduct) && $childProduct->getId())
            {
                $child_products_array[] = $childProduct;
            }
        }

        return $child_products_array;
    }

    /**
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    protected function _getConfigurableProductTypeModel()
    {
        if (is_null($this->_configurableProductTypeModel))
        {
            $this->_configurableProductTypeModel = Mage::getModel('catalog/product_type_configurable');
        }

        return $this->_configurableProductTypeModel;
    }
}
