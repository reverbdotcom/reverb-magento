## 0.8.3
* Accept offers can be set to yes/no on a per product basis
* Map description field to any field on your products
* Order pagination fix - if you have more than 24 orders per syncing period, we will now find them
* Mass updates now trigger reverb syncs
* Reverb sync fully backgrounded. Saving a product does not block while contacting Reverb. Note that this will introduce a delay of 1 minute or more between saving a product and the change going to Reverb depending on your cron schedule.

## 0.8.2
* Configurable product support (from 0.8.1)
* Make and model are synced on both create and update
* Make and model mapping can now successfully map to dropdowns and send the value of the dropdown
* If condition is not set, use the default condition
* Condition is chosen from a dropdown
* All nav correctly highlights the Reverb tab instead of Catalog tab

## 0.8.1
* Prior to this release, you could only use simple products with the Reverb extension. Now, configurable products are correctly supported. The associated simple product children of your configurable product will be pushed to Reverb as individual listings.

## 0.8.0
* Map make/model/price fields in the configuration screen to any attributes from Magento

## 0.7.0
* Fix issues with Magento Enterprise Edition related to curl library incompatibility

## 0.6.3
* Fixes incorrect ordering of category mappings resulting in categories not syncing to Reverb. Note that this release will wipe out your category mappings, and you'll have to reassign the mappings. Since they weren't working correctly to begin with, this should not be an issue.

## 0.6.2
* Magento version displayed in referrer string and settings screen so Reverb Support can identify customer version

## 0.6.1
* Buttons to delete successful sync tasks

## 0.6.0
* Categories and subcategories are now correctly assigned from the mapping screen
* Syncs categories on updates

## 0.5.2
* Fix order retrieval to run on a cron timer - it was only working when the sync button was pressed

## 0.5.0
* Don't sync categories on update to prevent mismapped category errors from impacting other updates

## 0.4.9
* Local pickup orders are correctly handled with a fake "Local Pickup" address (Magento requires addresses on orders)
* Condition can be set on a per-item basis with a dropdown
* Addresses with whitespace no longer cause issues
* Admin routing updated to comply with Magento Security Patch SUPEE-6788 / APPSEC-1034

## 0.4.8
* Unpaid order are not invoiced in Magento until they are paid

## 0.4.7
* Orders cancelled on Reverb will also cancel magento orders. If magento order can't be cancelled, an error will be reported in the order update sync grid.

## 0.4.6
* Set up category mappings to create products
* Ability to select default store for order creation for multi-store setups

## 0.4.5
* Orders are now created in the default store instead of the admin store. The end result is that order numbering should be consistent with existing orders instead of starting from 0001.

## 0.4.4
* Magento owner can select whether to sync accepted offers (unpaid orders) or only paid orders
* Performance improvements (thanks Steve Wolfe)

## 0.4.3
* Price, title, inventory sync are now available as switches in the settings screen. Only inventory is on by default.
* Image sync now works on update as well as creation.

## 0.4.2
* Turn off price and title sync on update. Only inventory will be synced. Version 0.4.3 will include global settings to turn these on and off.

## 0.4.1
* All reverb functionality consolidated under Reverb tab
* Sync images (listing create only)
* Fix handling of successful responses from listing create when attaching images

## 0.4.0

* Sync tracking information back to Reverb when marked as shipped in magento

## 0.3.9
* Fix "curl error #3" affecting certain versions of Magento

## 0.3.8

* Orders have a "fake" but consistent (across orders) buyer email a1b2c3@orders.reverb.com for use with other systems that rely on email as a unique key
* Default sync url to Reverb Sandbox in case one is not specified in settings
* Link to reverb item in the order invoice

## 0.3.7

* Manual retries of failed order syncs, useful for fixing inventory out of stock problems.
* Admin can control whether superuser override is used on orders. If not used, then orders will error out if the item is out of stock.
* Tasks in progress/failed are more clearly indicated in the order queue
