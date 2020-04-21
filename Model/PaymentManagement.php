<?php

namespace Getloy\GetloyMagentoGateway\Model;

use Getloy\GetloyMagentoGateway\Api\PaymentManagementInterface;
use Getloy\GetloyMagentoGateway\Gateway\Config\Config as GatewayConfig;
use Getloy\GetloyMagentoGateway\Model\CallbackResponse;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class PaymentManagement implements PaymentManagementInterface
{
    /**
     * @var GatewayConfig
     */
    protected $gatewayConfig;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var BillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * SessionInformationManagement constructor.
     *
     * @param GatewayConfig                     $gatewayConfig
     * @param CartRepositoryInterface           $quoteRepository
     * @param OrderRepositoryInterface          $orderRepository
     * @param PaymentDataObjectFactory          $paymentDataObjectFactory
     * @param QuoteIdMaskFactory                $quoteIdMaskFactory
     * @param InvoiceSender                     $invoiceSender
     * @param BillingAddressManagementInterface $billingAddressManagement
     * @param StoreManagerInterface             $storeManager
     * @param CartManagementInterface           $cartManagement
     * @param UrlInterface                      $url
     */
    public function __construct(
        GatewayConfig $gatewayConfig,
        CartRepositoryInterface $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        InvoiceSender $invoiceSender,
        BillingAddressManagementInterface $billingAddressManagement,
        StoreManagerInterface $storeManager,
        CartManagementInterface $cartManagement,
        UrlInterface $url
    ) {
        $this->gatewayConfig = $gatewayConfig;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->invoiceSender = $invoiceSender;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->storeManager = $storeManager;
        $this->cartManagement = $cartManagement;
        $this->url = $url;
    }

    protected function generateTransactionId($orderId)
    {
        $tstamp = base_convert(time(), 10, 36);
        return sprintf('MG-%s-%s', $orderId, $tstamp);
    }

    protected function validateTransactionId($transactionId, $orderId)
    {
        return 1 === preg_match(
            sprintf('/^MG-%d-[0-9a-z]{6}$/', $orderId),
            $transactionId
        );
    }

    protected function generatePayload(
        \Magento\Quote\Model\Quote $quote,
        $orderId
    ) {
        $transactionId  = $this->generateTransactionId($orderId);
        $totalAmount    = round($quote->getGrandTotal(), 2);
        $currency       = $this->storeManager
            ->getStore()->getBaseCurrency()->getCode();

        /* @var \Magento\Quote\Api\Data\AddressInterface */
        $billingAddress = $quote->getBillingAddress();
        
        $getloyMerchantKey = $this->gatewayConfig->getGetloyMerchantKey();
        $testMode          = $this->gatewayConfig->isSandbox();
        $paywayMerchantId  = $this->gatewayConfig->getPaywayMerchantId();
        $paywayMerchantKey = $this->gatewayConfig->getPaywayApiKey();

        $getloyGateway = new \Getloy\Gateway($getloyMerchantKey);
        $getloyGateway->registerPaymentProvider(
            \Getloy\PaymentProviders::PAYWAY_KH,
            [
                'testMode' => $testMode,
                'merchantId' => $paywayMerchantId,
                'merchantKey' => $paywayMerchantKey,
            ]
        );

        $orderItems = new \Getloy\TransactionDetails\OrderItems();

        foreach ($quote->getAllVisibleItems() as $item) {
            $orderItems->add(
                new \Getloy\TransactionDetails\OrderItem(
                    $item->getName() ?: '',
                    (int) round($item->getQty(), 0),
                    $item->getRowTotal(),
                    $item->getPrice()
                )
            );
        }

        $order = new \Getloy\TransactionDetails\OrderDetails(
            $totalAmount,
            $currency,
            null,
            $orderItems
        );


        $payee = new \Getloy\TransactionDetails\PayeeDetails(
            $billingAddress->getFirstname(),
            $billingAddress->getLastname(),
            $billingAddress->getEmail() ? $billingAddress->getEmail() : '',
            $billingAddress->getTelephone() ? $billingAddress->getTelephone() : ''
        );
        return $getloyGateway->widgetPayload(
            $transactionId,
            \Getloy\PaymentProviders::PAYWAY_KH,
            $order,
            $payee,
            $this->url->getUrl('rest/default/V1/getloy/payment/callback/'.$orderId)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createPayment(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    ) {
        /**
         * @var \Magento\Quote\Model\Quote $quote 
         */
        $quote = $this->quoteRepository->getActive($cartId);

        return $this->placeOrderAndGetPayload(
            $quote,
            $paymentMethod,
            $billingAddress
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createGuestPayment(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory
            ->create()
            ->load($cartId, 'masked_id');

        $billingAddress->setEmail($email);

        /**
         * @var \Magento\Quote\Model\Quote $quote 
         */
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());

        $quote->setBillingAddress($billingAddress);

        $quote->setCheckoutMethod(
            \Magento\Quote\Api\CartManagementInterface::METHOD_GUEST
        );

        $this->quoteRepository->save($quote);
       
        return $this->placeOrderAndGetPayload(
            $quote,
            $paymentMethod,
            $billingAddress
        );
    }

    protected function placeOrderAndGetPayload(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    ) {
        $quote->setBillingAddress($billingAddress);

        $this->quoteRepository->save($quote);

        $orderId = $this->cartManagement->placeOrder(
            $quote->getId(),
            $paymentMethod
        );

        /**
         * @var \Magento\Sales\Model\Order
         */
        $order = $this->orderRepository->get($orderId);

        $order->addStatusHistoryComment(__('PayWay payment session started.'))
            ->save();

        return [ [
            'payload' => $this->generatePayload($quote, $orderId),
        ] ];
    }

    /**
     * @param string $orderId
     * @param string $tid
     * @param string $status
     * @param string $amount_paid
     * @param string $currency
     * @param string $auth_hash_ext
     *
     * @return \Getloy\GetloyMagentoGateway\Model\CallbackResponse
     */
    public function handleCallback(
        $orderId,
        $tid,
        $status,
        $amount_paid,
        $currency,
        $auth_hash_ext
    ): CallbackResponse {
        $gateway = new \Getloy\Gateway($this->gatewayConfig->getGetloyMerchantKey());

        try {
            $callbackDetails = $gateway->validateCallback(
                [
                    'tid' => $tid,
                    'status' => $status,
                    'amount_paid' => $amount_paid,
                    'currency' => $currency,
                    'auth_hash_ext' => $auth_hash_ext
                ]
            );
        } catch (\Exception $e) {
            return new CallbackResponse('failed', 'malformed callback');
        }

        if (\Getloy\CallbackDetails::STATUS_SUCCESS !== $callbackDetails->status()) {
            return new CallbackResponse('failed', 'invalid transaction status');
        }

        if (!$this->validateTransactionId($tid, $orderId)) {
            return new CallbackResponse('failed', 'order ID mismatch');
        }

        try {
            /**
             * @var \Magento\Sales\Model\Order 
             */
            $order = $this->orderRepository->get((int) $orderId);
        } catch (\Exception $e) {
            return new CallbackResponse('failed', 'quote does not exist');
        }

        $totalAmount = round($order->getGrandTotal(), 2);
        $currency    = $order->getOrderCurrencyCode();

        if (abs($totalAmount - $callbackDetails->amountPaid()) > 0.01
            || $currency !== $callbackDetails->currency()
        ) {
            return new CallbackResponse('failed', 'invalid callback');
        }

        $order->addStatusHistoryComment(
            __(
                'PayWay payment complete. PayWay transaction ID: #%1.', 
                $tid
            )
        );

        /**
         * @var \Magento\Sales\Model\Order\Payment 
         */
        $payment = $order->getPayment();

        $payment->setTransactionId($tid);
        $payment->setCurrencyCode($callbackDetails->currency());
        $payment->registerCaptureNotification($callbackDetails->amountPaid());
        $payment->setAdditionalInformation('getloy_transaction_id', $tid);
        $payment->setAdditionalInformation('getloy_payment_method', 'PayWay');
        $payment->setAdditionalInformation('getloy_method_variant', 'default');
        $payment->save();

        /**
         * @var \Magento\Sales\Model\Order\Invoice 
         */
        $invoice = $payment->getCreatedInvoice();
        $this->invoiceSender->send($invoice);
        $order->addStatusHistoryComment(
            __(
                'You notified customer about invoice #%1.',
                $invoice->getIncrementId()
            )
        )
            ->setIsCustomerNotified(true)
            ->save();

        return new CallbackResponse('complete', 'callback processed successfully');
    }
}
