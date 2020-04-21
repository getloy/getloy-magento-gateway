<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Getloy\GetloyMagentoGateway\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

class Info extends ConfigurableInfo
{

    /**
     * @var string
     */
    protected $_template = 'Getloy_GetloyMagentoGateway::info.phtml';

    /**
     * Returns label
     *
     * @param string $field
     *
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * @param  \Magento\Sales\Model\Order\Payment $payment
     * @return string
     */
    protected function getPaymentStatus(
        \Magento\Sales\Model\Order\Payment $payment
    ) {
        if ((float) $payment->getAmountRefunded() > 0) {
            return __('Refunded');
        } else if ((float) $payment->getAmountCanceled() > 0) {
            return __('Cancelled');
        } else if ((float) $payment->getAmountPaid() === (float) $payment->getAmountOrdered()) {
            return __('Fully paid');
        } else {
            return __('Unpaid');
        }
    }

    /**
     * Prepare Getloy-specific payment information
     *
     * @param  \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $info = [
            (string) __('Status') => $this->getPaymentStatus($payment),
        ];

        $additionalInfo = $payment->getAdditionalInformation();
        if (array_key_exists('getloy_transaction_id', $additionalInfo)) {
            $info[ (string)__('Transaction ID') ] = $additionalInfo[
                'getloy_transaction_id'
            ];
        }

        return $transport->addData($info);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $additionalInfo = $this->getInfo()->getAdditionalInformation();

        if (array_key_exists('getloy_payment_method', $additionalInfo)) {
            return __('Paid with %1', $additionalInfo[ 'getloy_payment_method' ]);
        }
    }
}
