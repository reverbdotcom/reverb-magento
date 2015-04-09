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

## LICENSE

Copyright Reverb.com, LLC 2015.

This code is provided under the Apache 2.0 License.
For full details see: https://www.apache.org/licenses/LICENSE-2.0
