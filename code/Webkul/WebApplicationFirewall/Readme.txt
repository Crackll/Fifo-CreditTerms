#Installation

Magento 2 Application Firewall installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and then move "app" folder (inside "src" folder) into magento root directory.

Run Following Command via terminal
-----------------------------------

composer require geoip2/geoip2:~2.0
php bin/magento setup:upgrade
php bin/magento module:enable
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

#User Guide

For Magento 2 Application Firewall module's working process follow user guide : https://webkul.com/blog/magento2-web-security-module/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
