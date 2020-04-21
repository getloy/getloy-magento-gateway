<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Getloy\GetloyMagentoGateway\Gateway\Config;

use Magento\Payment\Gateway\Config\Config as BaseConfig;

// use Magento\Store\Model\StoreManagerInterface;
// use Magento\Framework\App\Config\ScopeConfigInterface;
// use Getloy\GetloyMagentoGateway\Model\Ui\ConfigProvider;
// use Magento\Store\Model\ScopeInterface;

class Config extends BaseConfig
{

    const KEY_ACTIVE = 'active';
    const KEY_SANDBOX = 'sandbox';
    const KEY_GETLOY_MERCHANT_KEY = 'getloy_merchant_key';
    const KEY_PAYWAY_TITLE = 'payway_title';
    const KEY_PAYWAY_MERCHANT_ID = 'payway_merchant_id';
    const KEY_PAYWAY_API_KEY_TEST = 'payway_api_key_test';
    const KEY_PAYWAY_API_KEY_PROD = 'payway_api_key_prod';
    const KEY_ACCEPTED_CURRENCIES = 'accepted_currencies';

    const PAYMENT_METHOD_PAYWAY_KH = 'payway_kh';
    const PAYMENT_METHOD_VARIANT_DEFAULT = 'default';

    /**
     * Determines if the environment is set as live (production) mode.
     *
     * @return bool
     */
    public function isLive()
    {
        return !$this->isSandbox();
    }

    /**
     * Determines if the environment is set as sandbox (test) mode.
     *
     * @return bool
     */
    public function isSandbox()
    {
        return (bool) $this->getValue(self::KEY_SANDBOX);
    }

    /**
     * Returns the currencies allowed for payment.
     *
     * @return array
     */
    public function getAcceptedCurrencies()
    {
        return ['USD'];
    }

    /**
     * Determines if the gateway is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Returns the payment method titles
     *
     * @return array
     */
    public function getMethodTitles()
    {
        return [
            self::PAYMENT_METHOD_PAYWAY_KH => [
                self::PAYMENT_METHOD_VARIANT_DEFAULT => (string) $this->getValue(self::KEY_PAYWAY_TITLE),
            ]
        ];
    }

    /**
     * Returns the GetLoy merchant key
     *
     * @return string
     */
    public function getGetloyMerchantKey()
    {
        return (string) $this->getValue(self::KEY_GETLOY_MERCHANT_KEY);
    }

    /**
     * Returns the PayWay merchant ID
     *
     * @return string
     */
    public function getPaywayMerchantId()
    {
        return (string) $this->getValue(self::KEY_PAYWAY_MERCHANT_ID);
    }

    /**
     * Returns the PayWay API key (sandbox)
     *
     * @return string
     */
    public function getPaywayApiKeySandbox()
    {
        return (string) $this->getValue(self::KEY_PAYWAY_API_KEY_TEST);
    }

    /**
     * Returns the PayWay API key (live)
     *
     * @return string
     */
    public function getPaywayApiKeyLive()
    {
        return (string) $this->getValue(self::KEY_PAYWAY_API_KEY_PROD);
    }

    /**
     * Returns the PayWay API key for the the currently active mode (live or sandbox)
     *
     * @return string
     */
    public function getPaywayApiKey()
    {
        if ($this->isSandbox()) {
            return $this->getPaywayApiKeySandbox();
        }
        return $this->getPaywayApiKeyLive();
    }
}
