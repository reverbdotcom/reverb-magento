# Reverb Magento Sample Integration

This is a demo application for integrating with Reverb's API. It is not to be
considered a fully functional app.

Since there are many different magento versions and installations out there, we
hope that by providing this sample integration, shops can customize it as
needed.

## What's working

Currently this extension syncs inventory from Magento to Reverb based on SKU.
It will also create new listings on Reverb if the SKU is not found.

The result of the sync is logged to a sync log available from the settings screen.

Only simple products are synced. Configurable products are not synced.

## Requirements

In order for the cron to successfully process the listings sync in parallel-thread manner, the magento cron needs to be
declared in the crontab via the following:

php cron.php -mdefault 1

or

/bin/sh /var/www/html/reverb_magento/cron.sh cron.php -mdefault 1 > /dev/null 2>&1 &

## What's not working

* Syncing configurable products
* Syncing images from magento to Reverb
* Syncing inventory (decrements/increments) from Reverb to Magneto [using webhooks](https://reverb.com/page/api#webhooks)
* Order syncing from Reverb to Magento

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request

## LICENSE

Copyright 2015 Reverb.com, LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
