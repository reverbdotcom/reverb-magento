# Reverb Magento Plugin

[![Join the chat at https://gitter.im/reverb-magento/Lobby](https://badges.gitter.im/reverb-magento/Lobby.svg)](https://gitter.im/reverb-magento/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

This is a Magento app for integrating with Reverb's API including product sync (magento->reverb) and order sync (reverb->magento).

While this plugin can and does work out of the box for many sellers, it is intended as a base for you to customize for your own magento usage. It is only tested on Magento Community 1.7 and 1.9. Enterprise Edition customers are advised to have their own developers evaluate and customize the plugin for usage.

Please read this entire README prior to installing the application.

## Features

* Create new draft listings on Reverb from Magento products, including image & category sync
* Control whether price/title/inventory syncs individualy.
* Sync updates for inventory from Magento to Reverb. 
* Sync orders from Reverb to Magento
* Sync shipping tracking information from Magento to Reverb
* Configurable products - children are synced as individual listings on Reverb
* Make/model/price/finish/year/shipping_profile_name can be mapped to attributes in your magento installation

## Professional Support

If you need help with your Magento install or integration of this plugin with your Magento instance, please contact these independent consultants for support. Please note that Reverb does not manage these relationships or support the plugin directly.

##### Sean Dunagan

Github: [dunagan5887](https://github.com/dunagan588700)

Email: [dunagan5887 at gmail.com](mailto:dunagan5887+at+gmail.com?Subject=Reverb%20Magento%20Plugin)

##### Timur Zaynullin

Github: [zztimur](https://github.com/zztimur)

Email: [zztimur at gmail.com](mailto:zztimur+at+gmail.com?Subject=Reverb%20Magento%20Plugin)

## FAQ

#### Q: Why aren't things synced in real time, or failing to sync at all?

If you're experiencing problems with your cron, please install [AOE Scheduler](https://www.magentocommerce.com/magento-connect/aoe-scheduler.html) to inspect the functioning of your cron.  

The Reverb sync runs on a cron (magento's scheduler)  that's set to every minute for product syncs and every two minutes for order syncing. This is done so that when you save a product we won't interfere with your normal magento functions, and do all the sync in the background.

However the design of Magento's cron means that other cron-based plugins that take a long time to run may interfere with each other. Reverb generally finishes its work in seconds, but we have seen plugins that can take many minutes to run, or even crash, preventing plugins like Reverb from finishing their work. 

If you're continuing to have cron issues, please install Reverb on a fresh magento instance without any other plugins as a test. If that works, the problem is with one of your other plugins. Please ensure you have no error messages in your cron and php logs prior to contacting Reverb Support.

#### Q: Why are my Reverb Make & Model incorrect or showing as "Unknown"

**Make & Model are guessed from the title unless you map those fields **. Use the configuration screen (System->Configuration->Reverb Configuration) to map make/model fields to attribute fields in your Magento installation. If you don't have structured make/model fields, we will attempt to guess them from the title, but this is not reliable.

#### Q: How can I map make/model and other fields?

If you don't already have make & model fields in your magento installation, you can add them by using the Catalog->Attributes section to add two new fields (for example, "reverb_make" and "reverb_model"). Then go to Catalog->Attributes->Attribute Sets and add those fields into your default attribute set so they appear on every product. Finally, go to (System->Configuration->Reverb Configuration) and map the make and model fields to your newly created fields. You can do the same for other reverb attributes such as finish/year/shipping_profile_name

#### Q: How can I set all my items to free shipping?

1. Set up a [Reverb Shipping Profile](https://reverb.com/my/selling/shipping_rates) with free shipping ($0), called "Free Shipping".
2. Add a magento attribute for reverb_shipping_profile from Catalog->Manage Attributes. Set a default value of "Free Shipping" (corresponding to the profile you created in step 1).
3. Add a mapping from Shipping Profile to your newly created attribute in the System->Configuration->Reverb Settings screen.

## Installation: Part 1 - Install the App

Please follow the instructions below to download and install the app. This assumes you have shell access to your server. If you have only FTP access, please download and unzip the app into /path/to/magento/htodcs/app

```bash
# Where your magento lives. This is the only part you have to manually modify.
export MAGENTO_PATH=/path/to/magento

# Download the release
cd /tmp && wget https://github.com/reverbdotcom/magento/archive/0.9.4.tar.gz

# Unzip the release
tar zxvf 0.9.4.tar.gz

# Copy everything from the app folder into your magento app
rsync -avzp magento-0.9.4/app/* $MAGENTO_PATH/htdocs/app/

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

## Usage - Listing Sync

The listing sync to Reverb can be triggered in two ways:

1. When you Save any Product in Magento, it will automaticaly sync to Reverb. Make sure you set "Sync to Reverb" to "Yes" on the bottom of the product page, and enable the Reverb Module in your global settings (see Part 3 of installation).

2. Bulk Sync. Under the Reverb menu item, select listing or order sync and use the Bulk Sync button in the upper right. The page will update with progress over time. Please note that very large catalogs (thousands of skus) may take an hour or more to fully sync. Please refresh the page to see the sync report.

## Usage - Order Sync

Orders are automatically synced on a one minute cron timer. If you aren't seeing orders, please visit the Order Creation tab under Reverb and click the button to manually sync them. Please report any issues with periodic syncing to the [Reverb Magento Support Group](https://groups.google.com/forum/#!forum/reverb-magento)

* **Orders are synced only 24 hours into the past** if you just installed the extension and want to sync older orders, please edit the file at app/code/community/Reverb/ReverbSync/Helper/Orders/Retrieval/Creation.php and change MINUTES_IN_PAST_FOR_CREATION_QUERY to the number in minutes you want to go into the past. For 3 days, use 3 * 60 * 24 = 4320
* You can select whether to sync all orders (including unpaid accepted offers) or only orders awaiting shipment, via the settings screen

## Notes on Bulk Sync

The bulk sync uses multiple threads (runs in parallel). It takes some time to spin up, so it may appear that nothing is happening for approximately 1 minute until your cron runs and starts picking up the jobs.

## Troubleshooting

### Bulk sync doesn't work

1. First, check the cron log in /path/to/magento/htdocs/var/log/cron.log
2. Enable logging (System->Configuration->Advanced->Developer->Log Settings
3. Let the cron run again (wait a minute), then check logs `tail -f /path/to/magento/htdocs/var/log/*`

### Blank pages or plugin doesn't load

Please make sure you've [cleared your magento cache](https://www.properhost.com/support/kb/23/How-To-Clear-The-Magento-Cache).

## Support and Announcements

Please join the [Reverb Magento Support Group](https://groups.google.com/forum/#!forum/reverb-magento)

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
