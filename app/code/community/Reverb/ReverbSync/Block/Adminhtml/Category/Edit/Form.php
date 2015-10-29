<?php
/**
 * Author: Sean Dunagan
 * Created: 9/11/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Category_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $reverb_category_options_array = null;
    protected $_translationHelper = null;
    protected $_categorySyncHelper = null;
    protected $_magento_reverb_category_mapping_array = null;

    protected function _prepareForm()
    {
        $helper = Mage::helper('ReverbSync');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getActionUrl(), 'method' => 'post'));
        $form->setUseContainer(true);
        $html_id_prefix = 'ReverbSync_';
        $form->setHtmlIdPrefix($html_id_prefix);

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend' => $helper->__('Magento to Reverb Category Mapping'), 'class'=>'fieldset-wide')
        );

        $this->setForm($form);
        $to_return = parent::_prepareForm();

        $this->populateFormFieldset($fieldset);

        return $to_return;
    }

    public function populateFormFieldset(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $magentoCategoryCollection = Mage::getModel('catalog/category')
                                        ->getCollection()
                                        ->addFieldToFilter('level', array('gt' => 0))
                                        ->addAttributeToSelect('name');

        foreach ($magentoCategoryCollection->getItems() as $magentoCategory)
        {
            $this->addMagentoCategorySelect($fieldset, $magentoCategory);
        }
    }

    public function addMagentoCategorySelect($fieldset, $magentoCategory)
    {
        $name = $magentoCategory->getName();
        $magento_category_id = $magentoCategory->getId();
        $element_id = 'magento_category_select_' . $magento_category_id;
        $reverb_category_options_array = $this->_getReverbCategoryOptionsArray();
        $reverb_category_id = $this->_getReverbIdByMagentoCategoryId($magento_category_id);

        $helper = $this->_getTranslationHelper();

        $fieldset->addField($element_id, 'select', array(
            'name'  => $this->_getCategorySyncHelper()->getReverbCategoryMapFormElementName($magento_category_id),
            'label' => $helper->__($name),
            'title' => $helper->__($name),
            'value'  => $reverb_category_id,
            'values'   => $reverb_category_options_array,
            'required' => false
        ));
    }

    public function getActionUrl()
    {
        $uri_path = $this->getAction()->getUriPathForAction('save');
        return $this->getUrl($uri_path);
    }

    protected function _getReverbIdByMagentoCategoryId($category_entity_id)
    {
        $mapping_array = $this->_getMagentoReverbCategoryMapping();
        return isset($mapping_array[$category_entity_id]) ? $mapping_array[$category_entity_id] : '';
    }

    protected function _getMagentoReverbCategoryMapping()
    {
        if (is_null($this->_magento_reverb_category_mapping_array))
        {
            $this->_magento_reverb_category_mapping_array =
                Mage::getResourceSingleton('reverbSync/category_magento_reverb_mapping')
                    ->getArrayMappingMagentoCategoryIdToReverbCategoryId();
        }

        return $this->_magento_reverb_category_mapping_array;
    }

    protected function _getReverbCategoryOptionsArray()
    {
        if (is_null($this->reverb_category_options_array))
        {
            $this->reverb_category_options_array = Mage::helper('ReverbSync/sync_category')
                                                        ->getReverbCategorySelectOptionsArray();
        }

        return $this->reverb_category_options_array;
    }

    protected function _getTranslationHelper()
    {
        if (is_null($this->_translationHelper))
        {
            $this->_translationHelper = Mage::helper('ReverbSync');
        }

        return $this->_translationHelper;
    }

    protected function _getCategorySyncHelper()
    {
        if (is_null($this->_categorySyncHelper))
        {
            $this->_categorySyncHelper = Mage::helper('ReverbSync/sync_category');
        }

        return $this->_categorySyncHelper;
    }
}
