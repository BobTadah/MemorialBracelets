<?php

namespace IWD\AuthCIM\Gateway\Request\Payment;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CaptureRequest
 * @package IWD\AuthCIM\Gateway\Request\Payment
 */
class CaptureRequest extends AbstractRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $this->setBuildSubject($buildSubject);

        if ($this->hasTransactionId()) {
            return $this->profileTransPriorAuthCapture();
        } else {
            return $this->profileTransAuthCapture();
        }
    }

    /**
     * @return array
     */
    private function profileTransPriorAuthCapture()
    {
        return [
            'root' => 'createTransactionRequest',
            'transactionRequest' => [
                'transactionType' => 'priorAuthCaptureTransaction',
                'amount' => $this->formatPrice($this->getAmount()),
                'refTransId' => $this->getTransId()
            ]
        ];
    }

    /**
     * @return array
     */
    private function profileTransAuthCapture()
    {
        $order = $this->getOrderAdapter();

        $request = [
            'root' => 'createTransactionRequest',
            'transactionRequest' => [
                'transactionType' => 'authCaptureTransaction',
                'amount' => $this->formatPrice($this->getAmount()),
                'profile' => [
                    'customerProfileId' => $this->getCard()->getCustomerProfileId(),
                    'paymentProfile' => [
                        'paymentProfileId' => $this->getCard()->getPaymentId()
                    ]
                ],
                'order' => [
                    'invoiceNumber' => substr($order->getOrderIncrementId(), 0, 20),
                    'description' => 'Authorize and capture order #' . $order->getOrderIncrementId(),
                ],
                'lineItems' => $this->getLineItems(),

                'tax' => $this->getTax(),
                'shipping' => $this->getShipping(),
                'shipTo' => $this->getShippingItems(),
                'transactionSettings' => $this->getTransactionSettings()
            ]
        ];

        //If it's a live account we can pass the solution id in the request.
        if (!$this->getConfig()->getIsSandboxAccount()) {
            $request['transactionRequest']['solution'] = [
                'id' => 'AAA175377'
            ];
        }

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getTax()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getShipping()
    {
        return null;
    }
}
