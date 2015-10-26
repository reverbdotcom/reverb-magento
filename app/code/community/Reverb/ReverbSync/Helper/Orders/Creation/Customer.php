<?php
/**
 * Author: Sean Dunagan
 * Created: 9/18/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation_Customer extends Reverb_ReverbSync_Helper_Orders_Creation_Sync
{
    const EXCEPTION_ADDING_CUSTOMER_NAME = 'An error occurred while trying to add the buyer\'s name to the quote while creating order with Reverb id #%s: %s';

    const BUYER_EMAIL_TEMPLATE = '%s@orders.reverb.com';

    public function addCustomerToQuote(stdClass $reverbOrderObject, Mage_Sales_Model_Quote $quoteToBuild)
    {
        $magentoCustomerObject = Mage::getModel('customer/customer');
        $reverb_order_number = $reverbOrderObject->order_number;

        try
        {
            if (isset($reverbOrderObject->buyer_name))
            {
                $buyer_name_string = $reverbOrderObject->buyer_name;
                list($first_name, $middle_name, $last_name) = $this->getExplodedNameFields($buyer_name_string);
                $magentoCustomerObject->setFirstname($first_name);
                $magentoCustomerObject->setMiddlename($middle_name);
                $magentoCustomerObject->setLastname($last_name);
            }

            if (isset($reverbOrderObject->buyer_id) && (!empty($reverbOrderObject->buyer_id)))
            {
                $buyer_id = $reverbOrderObject->buyer_id;
                $hashed_buyer_id = md5($buyer_id);
                $customer_email = sprintf(self::BUYER_EMAIL_TEMPLATE, $hashed_buyer_id);
                $magentoCustomerObject->setEmail($customer_email);
            }
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_ADDING_CUSTOMER_NAME, $reverb_order_number, $e->getMessage());
            $this->_logOrderSyncError($error_message);
        }

        $quoteToBuild->setCustomer($magentoCustomerObject);
        return true;
    }
}
