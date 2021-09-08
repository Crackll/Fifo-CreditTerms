#Installation

Magento2 Marketplace Auction module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and MpAuction(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/MpAuction/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#Uninstallation

Note: After uninstallation, all data and tables of the module will be deleted from the instance. It will completely uninstall the module.

Please follow the steps for uninstallation-

1.  Run Following Command via terminal
    -----------------------------------
    php bin/magento mpauction:disable
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly uninstalled

#Reinstallation

If this module has been uninstalled by using the upper Uninstallation process then Please follow the steps for reinstallation-

1.  Run Following Command via terminal
    -----------------------------------
    php bin/magento module:enable Webkul_MpAuction
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly reinstalled

#User Guide

For Magento2 Marketplace Auction module's working process follow user guide - http://webkul.com/blog/magento2-marketplace-seller-auction/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/



