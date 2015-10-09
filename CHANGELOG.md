## 0.4.4 (Unreleased)
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
