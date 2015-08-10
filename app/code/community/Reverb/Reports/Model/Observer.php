<?php

class Reverb_Reports_Model_Observer
{
    public function recordListingReport($observer)
    {
        try
        {
            $reverbListingWrapper = $observer->getReverbListing();
            Mage::helper('reverb_reports')->logListingSyncReport($reverbListingWrapper);
        }
        catch(Exception $e)
        {
            Mage::logException($e);
        }
    }
} 