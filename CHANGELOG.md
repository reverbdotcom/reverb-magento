## 0.3.8

* Orders have a "fake" but consistent (across orders) buyer email a1b2c3@orders.reverb.com for use with other systems that rely on email as a unique key
* Default sync url to Reverb Sandbox in case one is not specified in settings

## 0.3.7

* Manual retries of failed order syncs, useful for fixing inventory out of stock problems.
* Admin can control whether superuser override is used on orders. If not used, then orders will error out if the item is out of stock.
* Tasks in progress/failed are more clearly indicated in the order queue
