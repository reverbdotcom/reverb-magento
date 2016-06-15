<?php
/**
 * Author: Sean Dunagan
 * Created: 9/16/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Unique_Index
    extends Reverb_ReverbSync_Block_Adminhtml_Orders_Task_Index
{
    public function getTaskJobCodes()
    {
        return array(Reverb_ReverbSync_Model_Sync_Shipment_Tracking::JOB_CODE);
    }
}
