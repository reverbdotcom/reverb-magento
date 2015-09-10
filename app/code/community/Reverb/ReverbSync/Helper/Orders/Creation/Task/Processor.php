<?php
/**
 * Author: Sean Dunagan
 * Created: 9/9/15
 */

class Reverb_ReverbSync_Helper_Orders_Creation_Task_Processor
    extends Reverb_ProcessQueue_Helper_Task_Processor_Unique
{
    // Batch size reduced to avoid memory issues
    protected $_batch_size = 100;
}
