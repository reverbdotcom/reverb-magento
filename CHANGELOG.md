## Unreleased

* Orders have a "fake" but consistent (across orders) buyer email a1b2c3@orders.reverb.com for use with other systems that rely on email as a unique key

## 0.3.7

* Manual retries of failed order syncs, useful for fixing inventory out of stock problems.
* Admin can control whether superuser override is used on orders. If not used, then orders will error out if the item is out of stock.
* Tasks in progress/failed are more clearly indicated in the order queue
