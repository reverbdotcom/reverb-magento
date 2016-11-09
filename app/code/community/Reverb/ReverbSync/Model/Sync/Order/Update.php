<?php
/**
 * Author: Sean Dunagan
 * Created: 9/10/15
 */

class Reverb_ReverbSync_Model_Sync_Order_Update extends Reverb_ProcessQueue_Model_Task
{
    const ERROR_MAGENTO_ORDER_NOT_CREATED = 'No Magento order object was returned from the order creation helper';
    const ERROR_ORDER_NOT_CREATED = 'Reverb Order with id %s has not been created in the Magento system yet';
    const EXCEPTION_EXECUTING_STATUS_UPDATE = 'Exception occurred while executing the status update for order with magento entity id %s to status %s: %s';
    const EXCEPTION_CREATING_ORDER = 'An exception occurred while creating order with Reverb Order Number %s: %s';
    const SUCCESS_ORDER_STATUS_UPDATED = 'The order\'s status has been updated to %s';
    const NOTICE_ORDER_NOT_PAID = 'Reverb Order #%s has not yet been paid, and will not be created in the Magento system at this time as a result';

    protected $_orderCreationHelper = null;

    /**
     * @var null|Reverb_ReverbSync_Model_Source_Orderurl
     */
    protected $_orderCreationRetrievalUrlSource = null;

    /**
     * @var null|Reverb_ReverbSync_Model_Source_Order_Status
     */
    protected $_orderStatusSource = null;

    public function updateReverbOrderInMagento(stdClass $argumentsObject)
    {
        if (!Mage::helper('ReverbSync/orders_sync')->isOrderSyncEnabled())
        {
            $error_message = Mage::helper('ReverbSync/orders_sync')->logOrderSyncDisabledMessage();
            return $this->_returnAbortCallbackResult($error_message);
        }

        $reverb_order_number = $argumentsObject->order_number;
        // Check to ensure the order has been created
        $magento_order_entity_id = Mage::getResourceSingleton('reverbSync/order')
                                    ->getMagentoOrderEntityIdByReverbOrderNumber($reverb_order_number);

        if (empty($magento_order_entity_id))
        {
            /**
             * In this event, we will adhere to the following business logic
             * IF the user has "Paid Orders Awaiting Shipment" setting
             *      on update, poll the endpoint (still using the "all" endpoint as we do want all updates)
             *      if the status on an order is unpaid/pending_review/blocked, do NOT create the order
             *      otherwise create the order
             *
             * IF the user has the All Orders setting,
             *      on update, create the order regardless of status
             */
            if ($this->_shouldCreateOrderDueToUpdateTransmission($argumentsObject))
            {
                try
                {
                    $magentoOrder = $this->_getOrderCreationHelper()->createMagentoOrder($argumentsObject);
                    // Get the magento order entity id from the newly created order
                    if ((!is_object($magentoOrder)) || (!$magentoOrder->getId()))
                    {
                        // If the order is not a loaded object in the database, throw an exception
                        $error_message = Mage::helper('ReverbSync')
                                            ->__(self::ERROR_MAGENTO_ORDER_NOT_CREATED, $reverb_order_number);
                        throw new Exception($error_message);
                    }

                    $magento_order_entity_id = $magentoOrder->getId();
                }
                catch(Exception $e)
                {
                    // In this event, log the error and return an Abort status
                    $error_message = Mage::helper('ReverbSync')->__(self::EXCEPTION_CREATING_ORDER, $reverb_order_number,
                        $e->getMessage());
                    Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);
                    return $this->_returnAbortCallbackResult($error_message);
                }
            }
            else
            {
                // In this case, do not create the order and return a Complete status for the task
                // Once the order becomes paid, a new order update will be created in the Reverb system which will
                //      trigger creation of the order
                $notice_message = Mage::helper('ReverbSync')->__(self::NOTICE_ORDER_NOT_PAID, $reverb_order_number);
                return $this->_returnSuccessCallbackResult($notice_message);
            }
        }

        $reverb_order_status = $argumentsObject->status;

        return $this->_executeStatusUpdate($magento_order_entity_id, $reverb_order_status, $argumentsObject);
    }

    /**
     * Will return whether or not the order should be create in the Magento system as per the following logic:
     *
     * IF the user has "Paid Orders Awaiting Shipment" setting
     *      on update, poll the endpoint (still using the "all" endpoint as we do want all updates)
     *      if the status on an order is paid, create the order
     *      otherwise do NOT create the order
     *
     * IF the user has the All Orders setting,
     *      on update, create the order regardless of status
     *
     * @param stdClass $argumentsObject
     * @return bool
     */
    protected function _shouldCreateOrderDueToUpdateTransmission(stdClass $argumentsObject)
    {
        // Check if the user has set Order Creation to be scoped to "All Orders"
        if (!$this->_getOrderCreationRetrievalUrlSource()->shouldOnlySyncPaidOrders())
        {
            // The order should be created regardless of its update status
            return true;
        }
        // Check the update status for the order
        $reverb_order_status = $argumentsObject->status;
        $paid_order_statuses_array = $this->_getOrderStatusSourceSingleton()->getPaidOrderStatusesArray();
        $should_create_order = in_array($reverb_order_status, $paid_order_statuses_array);
        return $should_create_order;
    }

    /**
     * @param int $magento_order_entity_id
     * @param string $reverb_order_status
     * @param stdClass $argumentsObject
     * @return false|Mage_Core_Model_Abstract
     */
    protected function _executeStatusUpdate($magento_order_entity_id, $reverb_order_status, $argumentsObject)
    {
        try
        {
            // Start a database transaction
            Mage::getResourceSingleton('sales/order')->beginTransaction();

            // Fire a general event denoting that a Reverb order update transmission has been received
            Mage::dispatchEvent('reverb_order_update',
                array('order_entity_id' => $magento_order_entity_id,
                      'reverb_order_status' => $reverb_order_status,
                      'reverb_order_update_arguments_object' => $argumentsObject)
            );

            // Fire an event specific to the order status transmitted by Reverb
            $event_name = 'reverb_order_status_update_' . $reverb_order_status;
            Mage::dispatchEvent($event_name,
                                    array('order_entity_id' => $magento_order_entity_id,
                                          'reverb_order_status' => $reverb_order_status,
                                          'reverb_order_update_arguments_object' => $argumentsObject)
            );
            // Update the reverb_order_status field on the sales_flat_order table
            $updated_rows = Mage::getResourceSingleton('reverbSync/order')
                                ->updateReverbOrderStatusByMagentoEntityId($magento_order_entity_id, $reverb_order_status);

            Mage::getResourceSingleton('sales/order')->commit();
        }
        catch(Reverb_ReverbSync_Model_Exception_Order_Update_Status_Redundant $e)
        {
            // Assume we have already processed this order update
            Mage::getResourceSingleton('sales/order')->rollBack();
            return $this->_returnSuccessCallbackResult('The order has been updated');
        }
        catch(Exception $e)
        {
            Mage::getResourceSingleton('sales/order')->rollBack();

            $error_message = Mage::helper('ReverbSync')
                                ->__(self::EXCEPTION_EXECUTING_STATUS_UPDATE, $magento_order_entity_id,
                                        $reverb_order_status, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logOrderSyncError($error_message);

            return $this->_returnAbortCallbackResult($error_message);
        }

        $success_message = Mage::helper('ReverbSync')->__(self::SUCCESS_ORDER_STATUS_UPDATED, $reverb_order_status);
        return $this->_returnSuccessCallbackResult($success_message);
    }

    /**
     * @return Reverb_ReverbSync_Model_Source_Order_Status
     */
    protected function _getOrderStatusSourceSingleton()
    {
        if (is_null($this->_orderStatusSource))
        {
            $this->_orderStatusSource = Mage::getSingleton('reverbSync/source_order_status');
        }

        return $this->_orderStatusSource;
    }

    /**
     * @return Reverb_ReverbSync_Model_Source_Orderurl
     */
    protected function _getOrderCreationRetrievalUrlSource()
    {
        if (is_null($this->_orderCreationRetrievalUrlSource))
        {
            $this->_orderCreationRetrievalUrlSource = Mage::getSingleton('reverbSync/source_orderurl');
        }

        return $this->_orderCreationRetrievalUrlSource;
    }

    /**
     * @return Reverb_ReverbSync_Helper_Orders_Creation
     */
    protected function _getOrderCreationHelper()
    {
        if (is_null($this->_orderCreationHelper))
        {
            $this->_orderCreationHelper = Mage::helper('ReverbSync/orders_creation');
        }

        return $this->_orderCreationHelper;
    }
}
