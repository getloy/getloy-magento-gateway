<?php

namespace Getloy\GetloyMagentoGateway\Gateway\Validator;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface        $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
    ) {
        $this->config = $config;
        parent::__construct($resultFactory);
    }

    /**
     * Performs domain-related validation for business object
     *
     * @param  array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $storeId = $validationSubject[ 'storeId' ];
        $currency = $validationSubject[ 'currency' ];

        if ($currency !== $this->config->getValue('currency', $storeId)) {
            $isValid = false;
        }
        if (!\in_array($currency, [ 'USD' ])) {
            $isValid = false;
        }

        return $this->createResult($isValid);
    }
}
