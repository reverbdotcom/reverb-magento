# Reverb Magento Plugin

This is a Magento app for integrating with Reverb's API including product sync (magento->reverb) and order sync (reverb->magento). It is currently under heavy development. Please read this entire README prior to installing the application.

## Features

* Create new draft listings on Reverb from Magento products, including image & category sync
* Control whether price/title/inventory syncs individualy.
* Sync updates for inventory from Magento to Reverb. 
* Sync orders from Reverb to Magento
* Sync shipping number from Magento to Reverb

Only simple products are synced. Configurable products are not synced.

## Installation: Part 1 - Install the App

Please follow the instructions below to download and install the app. This assumes you have shell access to your server. If you have only FTP access, please download and unzip the app into /path/to/magento/htodcs/app

```bash
# Where your magento lives. This is the only part you have to manually modify.
export MAGENTO_PATH=/path/to/magento

# Download the release
cd /tmp && wget https://github.com/reverbdotcom/magento/archive/0.4.8.tar.gz

# Unzip the release
tar zxvf 0.4.8.tar.gz

# Copy everything from the app folder into your magento app
rsync -avzp magento-0.4.8/app/* $MAGENTO_PATH/htdocs/app/

# Clear your cache
rm -rf $MAGENTO_PATH/htdocs/var/cache
```

## Installation: Part 2 - Install the Cron

The cron is used to process the listing syncing queue. To see what's in your crontab, run `crontab -l`. Please ensure that your crontab contains one of the following lines:

    * * * * * /bin/sh -c "php /path/to/magento/htdocs/cron.php -mdefault 1 > /path/to/magento/htdocs/var/log/cron.log"

or

    * * * * *  /bin/sh -c "/path/to/magento/htdocs/cron.sh cron.php -mdefault 1 > /path/to/magento/htdocs/var/log/cron.log 2>&1"

If your crontab does not contain either of these lines, please use `crontab -e` to edit it and copy the second line (`cron.sh`) into your crontab.


## Installation: Part 3 - Configuration

* In Magento Admin, go to System -> Configuration -> Reverb Configuration
* Put in your API Key (grab it from https://reverb.com/my/api_settings)
* Select Yes for Enable Reverb Module to turn on the sync
* If you also want to create drafts for skus that don't exist on Reverb, select "Enable Listing Creation" in the Reverb Default section.

## Usage

The sync to Reverb can be triggered in two ways:

1. When you Save any Product in Magento, it will automaticaly sync to Reverb. Make sure you set "Sync to Reverb" to "Yes" on the bottom of the product page, and enable the Reverb Module in your global settings (see Part 3 of installation).

2. Bulk Sync. Under the Reverb menu item, select listing or order sync and use the Bulk Sync button in the upper right. The page will update with progress over time. Please note that very large catalogs (thousands of skus) may take an hour or more to fully sync. Please refresh the page to see the sync report.

## Notes on Bulk Sync

The bulk sync uses multiple threads (runs in parallel). It takes some time to spin up, so it may appear that nothing is happening for approximately 1 minute until your cron runs and starts picking up the jobs.

## Troubleshooting

### Bulk sync doesn't work

1. First, check the cron log in /path/to/magento/htdocs/var/log/cron.log
2. Enable logging (System->Configuration->Advanced->Developer->Log Settings
3. Let the cron run again (wait a minute), then check logs `tail -f /path/to/magento/htdocs/var/log/*`

### Blank pages or plugin doesn't load

Please make sure you've [cleared your magento cache](https://www.properhost.com/support/kb/23/How-To-Clear-The-Magento-Cache).

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
