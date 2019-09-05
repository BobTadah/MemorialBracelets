<?php

namespace IWD\OrderManager\Model\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use IWD\OrderManager\Model\Payment\Method\AuthorizeCim;
use IWD\OrderManager\Model\Payment\Method\Braintree;
use IWD\OrderManager\Helper\Data;

/**
 * Class Payment
 * @package IWD\OrderManager\Model\Payment
 */
class Payment extends AbstractModel
{
    /**
     * Order
     * @var \IWD\OrderManager\Model\Order\Order
     */
    private $order;

    /**
     * @var AuthorizeCim
     */
    private $authorizeCim;

    /**
     * @var Braintree
     */
    private $braintree;

    /**
     * @var Data
     */
    private $configHelper;

    /**
     * Payment constructor.
     * @param Data $configHelper
     * @param Braintree $braintree
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AuthorizeCim $authorizeCim
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Data $configHelper,
        Braintree $braintree,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        AuthorizeCim $authorizeCim,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->configHelper = $configHelper;
        $this->braintree = $braintree;
        $this->authorizeCim = $authorizeCim;
    }

    /**
     * Setter For Order
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Getter For Order
     * @return \IWD\OrderManager\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Re-authorize Payment
     * @return boolean
     */
    public function reauthorizePayment()
    {
        try {
            $order = $this->getOrder();
            $payment = $order->getPayment();
            $paymentMethod = $payment->getMethod();

            $authorizedAmount = $payment->getBaseAmountAuthorized();
            $newOrderedAmount = $order->getBaseGrandTotal();

            /**
             * Authorized (but do not captured) more then we need now
             * Ex. (authorized $100, need $80)
             */
            if (!$order->hasInvoices() && $authorizedAmount >= $newOrderedAmount) {
                return true;
            }

            switch ($paymentMethod) {
                case 'free':
                case 'checkmo':
                case 'purchaseorder':
                    return true;
                case 'iwd_authcim':
                    return $this->reauthorizeIWDAuthorizenetCIM();
                case 'braintree':
                    return $this->reauthorizeBraintree();

            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Re-authorize Authorize.Net CIM
     * @return bool
     * @throws LocalizedException
     */
    private function reauthorizeIWDAuthorizenetCIM()
    {
        return $this->authorizeCim
            ->setOrder($this->getOrder())
            ->reauthorize();
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    private function reauthorizeBraintree()
    {
        if ($this->configHelper->checkBraintreeReauthorization()) {
            return $this->braintree
                ->setOrder($this->getOrder())
                ->reauthorize();
        }
       return true;
    }
}
