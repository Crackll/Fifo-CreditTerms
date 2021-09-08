#Installation

Magento2 Customer Approval module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and Customer Approval(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory magento/app/code/Webkul/CustomerApproval/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

Now module is properly installed

#Uninstallation

Please follow the steps for complete uninstallation-

php bin/magento module:uninstall Webkul_CustomerApproval
php bin/magento cache:flush
php bin/magento indexer:reindex

#User Guide

For Magento2 Customer Approval module's working process follow user guide : https://webkul.com/blog/magento2-b2b-customer-approval/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/


