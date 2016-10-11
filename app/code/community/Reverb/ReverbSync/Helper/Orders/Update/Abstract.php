<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 11/7/15
 */

/**
 * Class Reverb_ReverbSync_Helper_Orders_Update_Abstract
 */
abstract class Reverb_ReverbSync_Helper_Orders_Update_Abstract extends Mage_Core_Helper_Abstract
{
    const ORDER_CAN_NOT_BE_UPDATED = 'The Magento order with increment id %s can not be %s: %s';

    const REASON_PAYMENT_REVIEW = 'The order is in payment review state';
    const REASON_HOLD = 'The order is in the hold state';
    const REASON_INVOICED = 'The order has already been invoiced';
    const REASON_CANCELLED = 'The order has already been cancelled';
    const REASON_COMPLETE = 'The order has already been marked as complete';
    const REASON_CLOSED = 'The order has already been marked as closed';
    const REASON_DEFAULT = 'The order can not be cancelled';

    /**
     * @return string
     */
    abstract public function getUpdateAction();

    /**
     * @var null|Reverb_ReverbSync_Helper_Orders_Creation_Address
     */
    protected $_orderAddressCreationHelper = null;

    /**
     * Returns the updated order shipping address for the $magentoOrder order if $orderArgumentObject contains
     *      different address data values than those on the $magentoOrder object's shipping address
     * Returns null otherwise
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param stdClass $orderArgumentObject
     * @return Mage_Sales_Model_Order_Address|null
     */
    public function updateOrderShippingAddressIfNecessary(Mage_Sales_Model_Order $magentoOrder, stdClass $orderArgumentObject)
    {
        // Get the shipping address for the order
        $orderShippingAddress = $magentoOrder->getShippingAddress();
        // Get the shipping address defined in the $argumentObject
        $argumentObjectShippingAddress = $orderArgumentObject->shipping_address;
        $argumentsObjectOrderShippingAddress = $this->_getOrderAddressCreationHelper()
                                                    ->getCustomerAddressForOrderByArgumentsObject($argumentObjectShippingAddress);
        $argumentsObjectOrderShippingAddress->implodeStreetAddress();
        // Check to see if the data points between the $orderShippingAddress and $argumentsObjectOrderShippingAddress
        //      objects differ
        $arguments_object_order_data_array = $argumentsObjectOrderShippingAddress->getData();
        // Reverb will return abbreviated state values for the region. Magento will convert the region to the full
        //  state name since getCustomerAddressForOrderByArgumentsObject() will return the region id for the address
        //  As such, we don't want the region field to trigger an update, we will only trigger an update if the
        //  region_id value is different
        // We expect the 'region' index to be set on $arguments_object_order_data_array, but check isset() just in case
        if (isset($arguments_object_order_data_array['region']))
        {
            unset($arguments_object_order_data_array['region']);
        }

        $has_a_field_been_updated = false;
        foreach($arguments_object_order_data_array as $field => $arguments_object_value)
        {
            // Trim the $arguments_object_value before comparing to the shipping address's value to avoid making
            //  superficial updates to the address object
            $arguments_object_value = trim($arguments_object_value);
            $order_address_value = $orderShippingAddress->getData($field);
            if ($order_address_value != $arguments_object_value)
            {
                // Update the value
                $orderShippingAddress->setData($field, $arguments_object_value);
                $has_a_field_been_updated = true;
            }
        }
        // Check if any fields were updated on the order's shipping address
        if ($has_a_field_been_updated)
        {
            // If so, return the updated order address object
            $orderShippingAddress->save();
        }
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param string                 $reason
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order_Update
     */
    protected function _throwCanNotUpdateException(Mage_Sales_Model_Order $magentoOrder, $reason)
    {
        $error_message = $this->__(self::ORDER_CAN_NOT_BE_UPDATED, $magentoOrder->getIncrementId(),
                                   $this->getUpdateAction(), $reason);
        throw new Reverb_ReverbSync_Model_Exception_Data_Order_Update($error_message);
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order_Update
     */
    protected function _inspectWhyCanNotUpdateAndThrowException(Mage_Sales_Model_Order $magentoOrder)
    {
        if ($magentoOrder->isPaymentReview())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_PAYMENT_REVIEW);
        }
        if ($magentoOrder->canUnhold())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_HOLD);
        }
        if ($magentoOrder->isCanceled())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_CANCELLED);
        }

        $state = $magentoOrder->getState();
        if ($state === Mage_Sales_Model_Order::STATE_COMPLETE)
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_COMPLETE);
        }
        if ($state === Mage_Sales_Model_Order::STATE_CLOSED)
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_CLOSED);
        }

        $allInvoiced = $this->_isOrderAlreadyFullyInvoiced($magentoOrder);
        if ($allInvoiced)
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::REASON_INVOICED);
        }

        $this->_throwCanNotUpdateException($magentoOrder, self::REASON_DEFAULT);
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return bool
     */
    protected function _isOrderAlreadyFullyInvoiced(Mage_Sales_Model_Order $magentoOrder)
    {
        $allInvoiced = true;
        foreach ($magentoOrder->getAllItems() as $item) {
            /* @var Mage_Sales_Model_Order_Item $item */
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        return $allInvoiced;
    }

    /**
     * @return Reverb_ReverbSync_Helper_Orders_Creation_Address
     */
    protected function _getOrderAddressCreationHelper()
    {
        if (is_null($this->_orderAddressCreationHelper))
        {
            $this->_orderAddressCreationHelper = Mage::helper('ReverbSync/orders_creation_address');
        }
    
        return $this->_orderAddressCreationHelper;
    }
}
