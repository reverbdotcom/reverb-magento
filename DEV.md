For development, we are using `modman` to manage installing and updating this plugin.

## Using Modman

Install it

    bash < <(wget -q --no-check-certificate -O - https://raw.github.com/colinmollenhour/modman/master/modman-installer)

Init it (one time)

    cd /home/bitnami/apps/magento/htdocs
    modman init

Pull the plugin (one time)

    modman clone https://github.com/reverbdotcom/reverb-magento

Update it

    modman update reverb-magento

## Modman branches

    modman remove reverb-magento
    modman clone https://github.com/reverbdotcom/reverb-magento --branch foo
