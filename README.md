# Reverb Magento Sample Integration

This is a demo Magento application for integrating with Reverb's API. It is a functional way to sync your listing inventory with Reverb, but is under development. 

Since there are many different magento versions and installations out there, we hope that by providing this sample integration, shops can customize it as needed.


## Installation: Part 1 - Install the App

Please follow the instructions below to download and install the app. This assumes you have shell access to your server. If you have only FTP access, please download and unzip the app into /path/to/magento/htodcs/app

```bash
# Where your magento lives. This is the only part you have to manually modify.
export MAGENTO_PATH=/path/to/magento

# Download the release
cd /tmp && wget https://github.com/reverbdotcom/magento/archive/0.2.0.tar.gz

# Unzip the release
tar zxvf 0.2.0.tar.gz

# Copy everything from the app folder into your magento app
rsync -avzp magento-0.2.0/app/* $MAGENTO_PATH/htdocs/app/

# Clear your cache
rm -rf $MAGENTO_PATH/htdocs/var/cache
```

## Installation: Part 2 - Install the Cron

The cron is used to process the listing syncing queue. To see what's in your crontab, run `crontab -l`. Please ensure that your crontab contains one of the following lines:

    * * * * * php /path/to/magento/htdocs/cron.php -mdefault 1

or

    * * * * *  /bin/sh /path/to/magento/htdocs/cron.sh cron.php -mdefault 1 > /dev/null 2>&1 &

If your crontab does not contain either of these lines, please use `crontab -e` to edit it and copy the first line in there (not both). 


## Installation: Part 3 - Configuration

* In Magento Admin, go to System -> Configuration -> Reverb Configuration
* Put in your API Key (grab it from (https://reverb.com/my/account/settings)[https://reverb.com/my/account/settings])
* Select Yes for Enable Reverb Module to turn on the sync
* If you also want to create drafts for skus that don't exist on Reverb, select "Enable Listing Creation" in the Reverb Default section.

## Usage

The sync to Reverb can be triggered in two ways:
1. When you Save any Product in Magento, it will automaticaly sync to Reverb (if you have the sync settings enabled).
2. Bulk Sync. Visit Catalog->Reverb Bulk Sync, and press the "Bulk Product Sync" button in the upper right. The page will update with progress over time. Please note that very large catalogs (thousands of skus) may take an hour or more to fully sync.

## Notes on Bulk Sync

The bulk sync uses multiple threads (runs in parallel). It takes some time to spin up, so it may appear that nothing is happening for approximately 1 minute until your cron runs and starts picking up the jobs.

## What's working

Currently this extension syncs inventory from Magento to Reverb based on SKU.

It will also create new listings on Reverb if the SKU is not found. The option to turn on or off listing creation is available in the global settings screen.

The result of the sync is logged to a sync log available from the settings screen.

Only simple products are synced. Configurable products are not synced.

## What's not working

* Syncing configurable products
* Syncing images from magento to Reverb
* Syncing inventory (decrements/increments) from Reverb to Magneto [using webhooks](https://reverb.com/page/api#webhooks)
* Order syncing from Reverb to Magento
* Category mapping - from Magento categories to Reverb categories

## Advanced cron usage (optional)

Only one of these should be included in the crontab, not both. Also the schedule can be set to be less frequent than every minute if desired, but this would prevent the Reverb listing sync parallel execution threads from being started every other minute, which is the time defined in the config.xml file. It is recommended that if the Magento crontab schedule defined above is less often than every minute, the Reverb listing sync crontab job should have its schedule set to occur half as often as the Magento crontab; this will prevent the Reverb listing sync from blocking out other cron functionality once a Bulk Product Sync is triggered.

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
