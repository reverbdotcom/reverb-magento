<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 11/7/15
 */

class Reverb_ReverbSync_Helper_Orders_Update_Paid extends Reverb_ReverbSync_Helper_Orders_Update_Abstract
{
    const NO_PRODUCTS_INVOICED = 'The invoice did not contain any products';

    public function getUpdateAction()
    {
        return 'invoiced';
    }

    /**
     * This method does not catch exceptions as it expects the calling block to catch them by design
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param string                 $reverb_order_status
     */
    public function executeMagentoOrderPaid(Mage_Sales_Model_Order $magentoOrder, $reverb_order_status)
    {
        $invoice = $this->_initInvoice($magentoOrder);
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->register();

        $transactionSave = Mage::getModel('core/resource_transaction')
                               ->addObject($invoice)
                               ->addObject($invoice->getOrder());
        $transactionSave->save();
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return Mage_Sales_Model_Order_Invoice
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order_Update
     */
    protected function _initInvoice(Mage_Sales_Model_Order $magentoOrder)
    {
        if (!$magentoOrder->canInvoice())
        {
            $this->_inspectWhyCanNotUpdateAndThrowException($magentoOrder);
        }

        $magentoInvoice = Mage::getModel('sales/service_order', $magentoOrder)->prepareInvoice(array());
        if (!$magentoInvoice->getTotalQty())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::NO_PRODUCTS_INVOICED);
        }

        return $magentoInvoice;
    }
}
