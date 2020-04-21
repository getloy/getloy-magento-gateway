# GetLoy payment gateway for Magento 2 (supports PayWay by ABA Bank)


## Install module via composer

```sh
composer require getloy/getloy-magento-gateway
bin/magento module:enable --clear-static-content Getloy_GetloyMagentoGateway
bin/magento setup:upgrade
bin/magento cache:clean
bin/magento setup:di:compile
```
