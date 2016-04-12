<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 3/22/16
 */

class Reverb_ReverbSync_Helper_Category extends Mage_Core_Helper_Abstract
{
    const EXCEPTION_REMOVING_CATEGORY = 'An exception occurred while attempting to remove Reverb category with id %s: %s';

    public function removeCategoriesWithoutUuid()
    {
        $categoriesWithoutUuid = Mage::getModel('reverbSync/category_reverb')
                                    ->getCollection()
                                    ->addFieldToFilter(Reverb_ReverbSync_Model_Category_Reverb::UUID_FIELD, '');

        foreach($categoriesWithoutUuid->getItems() as $categoryWithoutUuid)
        {
            try
            {
                $categoryWithoutUuid->delete();
            }
            catch(Exception $e)
            {
                $error_message = $this->__(self::EXCEPTION_REMOVING_CATEGORY, $categoryWithoutUuid->getId(),
                                           $e->getMessage());
                Mage::log($error_message, null, 'reverb_category_uuid_to_slug_mapping.log', true);
            }
        }
    }
}
