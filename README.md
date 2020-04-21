# GetLoy Payment Gateway for Magento 2 (supports PayWay by ABA Bank)

[![Latest Stable Version](https://poser.pugx.org/getloy/getloy-magento-gateway/v/stable)](https://packagist.org/packages/getloy/getloy-magento-gateway) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/getloy/getloy-magento-gateway/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/getloy/getloy-magento-gateway/?branch=master) [![Total Downloads](https://poser.pugx.org/getloy/getloy-magento-gateway/downloads)](https://packagist.org/packages/getloy/getloy-magento-gateway) [![License](https://poser.pugx.org/getloy/getloy-magento-gateway/license)](https://packagist.org/packages/getloy/getloy-magento-gateway)


## Install module via composer

```sh
composer require getloy/getloy-magento-gateway
bin/magento module:enable --clear-static-content Getloy_GetloyMagentoGateway
bin/magento setup:upgrade
bin/magento cache:clean
bin/magento setup:di:compile
```
