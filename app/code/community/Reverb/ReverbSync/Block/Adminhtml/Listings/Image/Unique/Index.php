<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */

class Reverb_ReverbSync_Block_Adminhtml_Listings_Image_Unique_Index
    extends Reverb_ProcessQueue_Block_Adminhtml_Unique_Index
{
    public function getTaskCodeToFilterBy()
    {
        return 'listing_image_sync';
    }

    protected function _expediteTasksButtonLabel()
    {
        return 'Expedite Image Sync Tasks';
    }

    protected function _getHeaderTextTemplate()
    {
        return '%s of %s Listings Image Sync Tasks have completed syncing';
    }
}
