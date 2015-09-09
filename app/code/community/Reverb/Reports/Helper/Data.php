<?php

/**
 * Reports default helper
 *
 * @category    Reverb
 * @package     Reverb_Reports
 */
class Reverb_Reports_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function logListingSyncReport($listingWrapper)
    {
        try
        {
            // Currently, there should only be one row in the table per product_id
            $magentoProduct = $listingWrapper->getMagentoProduct();
            if ((!is_object($magentoProduct)) || (!$magentoProduct->getId()))
            {
                // This likely occurs as a result of an exception during a sync attempt
                // This should not occur, but we should handle the case where it does
                return;
            }

            $product_id = $magentoProduct->getId();
            $reverbReportObject = Mage::getModel('reverb_reports/reverbreport')->load($product_id);
            $reverbReportObject->populateWithDataFromListingWrapper($listingWrapper);
            $reverbReportObject->save();
        }
        catch(Exception $e)
        {
            Mage::logException($e);
        }
    }
}
