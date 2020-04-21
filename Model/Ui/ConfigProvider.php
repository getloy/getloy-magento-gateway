<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Getloy\GetloyMagentoGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Getloy\GetloyMagentoGateway\Gateway\Config\Config;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'getloy_magento_gateway';

    /**
     * @var ScopeConfigInterface 
     */
    protected $scopeConfig;

    /**
     * @var Config 
     */
    protected $config;

    /**
     * @var Session 
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface 
     */
    protected $storeManager;

    /**
     * @var AssetRepository 
     */
    protected $assetRepo;

    /**
     * @var Currency 
     */
    protected $currency;

    /**
     * @var Quote 
     */
    protected $quote;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param Session               $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory       $currencyFactory
     * @param AssetRepository       $assetRepo
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        AssetRepository $assetRepo
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->config = new Config($scopeConfig, self::CODE);
        $this->currency = $currencyFactory->create();
        $this->quote = $this->checkoutSession->getQuote();
        $this->assetRepo = $assetRepo;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        // $storeId = $this->session->getStoreId();
        return [
            'payment' => [
                self::CODE => [
                    'active' => $this->isActive(),
                    'sandbox' => $this->config->isSandbox(),
                    'title' => $this->config->getMethodTitles()[
                        $this->config::PAYMENT_METHOD_PAYWAY_KH
                    ][
                        $this->config::PAYMENT_METHOD_VARIANT_DEFAULT
                    ],
                    'payment_method_logos' => $this->paymentMethodLogos(),
                ]
            ]
        ];
    }

    public function paymentMethodLogos()
    {
        return [
            [
                'title' => 'Visa',
                'logo' => $this->assetRepo->getUrl(
                    'Getloy_GetloyMagentoGateway::images/visa.svg'
                ),
            ],
            [
                'title' => 'MasterCard',
                'logo' => $this->assetRepo->getUrl(
                    'Getloy_GetloyMagentoGateway::images/mastercard.svg'
                ),
            ],
            [
                'title' => 'UnionPay',
                'logo' => $this->assetRepo->getUrl(
                    'Getloy_GetloyMagentoGateway::images/unionpay.svg'
                ),
            ],
            [
                'title' => 'ABA Pay',
                'logo' => $this->assetRepo->getUrl(
                    'Getloy_GetloyMagentoGateway::images/aba-pay.svg'
                ),
            ],
        ];
    }

    public function isActive()
    {
        if (!$this->config->isActive()) {
            return false;
        }
        return (bool) in_array(
            $this->quote->getQuoteCurrencyCode(),
            $this->config->getAcceptedCurrencies()
        );
    }
}
