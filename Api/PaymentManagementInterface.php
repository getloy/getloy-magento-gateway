<?php

namespace Getloy\GetloyMagentoGateway\Api;

interface PaymentManagementInterface
{
    /**
     * @param  string                                   $cartId
     * @param  \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param  \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return array
     */
    public function createPayment(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    );

    /**
     * @param  string                                   $cartId
     * @param  string                                   $email
     * @param  \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param  \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return array
     */
    public function createGuestPayment(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    );

    /**
     * @param  string $orderId
     * @param  string $tid
     * @param  string $status
     * @param  string $amount_paid
     * @param  string $currency
     * @param  string $auth_hash_ext
     * @return \Getloy\GetloyMagentoGateway\Model\CallbackResponse
     */
    public function handleCallback(
        $orderId,
        $tid,
        $status,
        $amount_paid,
        $currency,
        $auth_hash_ext
    ): \Getloy\GetloyMagentoGateway\Model\CallbackResponse;
}
