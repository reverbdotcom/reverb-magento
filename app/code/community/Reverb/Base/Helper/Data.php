<?php
/**
 * Author: Sean Dunagan
 * Created: 9/15/15
 */

class Reverb_Base_Helper_Data
{
    public function isAdminLoggedIn()
    {
        $admin_user_id = Mage::helper('adminhtml')->getCurrentUserId();
        return (!empty($admin_user_id));
    }
} 