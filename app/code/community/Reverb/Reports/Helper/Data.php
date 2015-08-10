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
