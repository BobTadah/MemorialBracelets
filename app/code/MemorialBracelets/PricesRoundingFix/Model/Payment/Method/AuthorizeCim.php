<?php

namespace MemorialBracelets\PricesRoundingFix\Model\Payment\Method;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Sales\Api\OrderPaymentRepositoryInterface as OrderPaymentRepository;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AuthorizeCim
 * @package IWD\OrderManager\Model\Payment\Method
 */
class AuthorizeCim extends \IWD\OrderManager\Model\Payment\Method\AuthorizeCim
{
    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    private $payment;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        OrderPaymentRepository $orderPaymentRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $orderPaymentRepository,
            $resource,
            $resourceCollection,
            $data
        );
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function reauthorize()
    {
        $order = $this->getOrder();
        $this->payment = $order->getPayment();

        $newOrderedAmount = $this->fixRounding($order->getBaseGrandTotal());
        $authorizedAmount = $this->fixRounding($this->payment->getBaseAmountAuthorized());

        /**
         * @var $paymentAuthorizenet \Magento\Sales\Model\Order\Payment
         */
        $paymentAuthorizenet = $this->payment->getMethodInstance();
        $newTotal = $newOrderedAmount;//$order->getCurrentOrderTotal();
        $oldTotal = $authorizedAmount;//$order->getOldTotal();

        /**
         * Authorized (but do not captured) less then we need now.
         * Ex. (authorized $80 but we need $100 - so AUTHORIZE +$20)
         */
        if (!$order->hasInvoices() && $authorizedAmount < $newOrderedAmount) {
            $amount = $newOrderedAmount - $authorizedAmount;
            if (!$paymentAuthorizenet->authorize($this->payment, $amount)) {
                throw new LocalizedException(__('Error in re-authorizing payment.'));
            }

            $this->addTransactionComment(Transaction::TYPE_AUTH, 'Authorized amount of %1 online.', $amount);

            $this->payment->setAmountAuthorized($order->getGrandTotal());
            $this->payment->setBaseAmountAuthorized($newOrderedAmount);
            $this->savePayment();
            return true;
        }

        if ($oldTotal > $newTotal) {
            /**
             * Captured/settled more when we need now (amount was decreased [-])
             * Ex. (captured $100 but we need $80 - so REFUND -$20)
             */
            $amount = $oldTotal - $newTotal;
            $this->payment->setBaseAmountAuthorized($newOrderedAmount);
            return $this->refund($amount);
        } else {
            /**
             * Captured/settled less when we need now (amount was increased [+])
             * Ex. (captured $80 but we need $100 - so CAPTURE +$20)
             */
            $amount = $newTotal - $oldTotal;
            $this->payment->setBaseAmountAuthorized($newOrderedAmount);
            $this->capture($amount);
            return true;
        }
    }

    /**
     * @param $amount
     */
    private function refund($amount)
    {
        $order = $this->getOrder();
        $this->payment = $order->getPayment();

        $this->payment->setAdditionalInformation('last_transaction_id', null);

        $this->payment->getMethodInstance()->refund($this->payment, $amount);

        $this->savePayment();
    }

    /**
     * @param $amount
     */
    private function capture($amount)
    {
        $order = $this->getOrder();
        $this->payment = $order->getPayment();

        $this->payment->getMethodInstance()->capture($this->payment, $amount);

        //$this->addTransactionComment(Transaction::TYPE_CAPTURE, 'Captured amount of %1 online.', $amount);
        $this->updatePaymentTotals($order);
        $this->savePayment();

        $this->updateInvoice();
    }

    /**
     * @param $type
     * @param $message
     * @param $amount
     */
    private function addTransactionComment($type, $message, $amount)
    {
        $transaction = $this->payment->addTransaction($type);
        $message = $this->payment->prependMessage(__($message, $this->formatPrice($amount)));
        $this->payment->addTransactionCommentsToOrder($transaction, $message);
    }

    /**
     * @param $order
     */
    private function updatePaymentTotals($order)
    {
        $newBaseOrderedAmount = round($order->getBaseGrandTotal(), 2);
        $newOrderedAmount = round($order->getGrandTotal(), 2);

        $this->payment->setAmountAuthorized($newOrderedAmount);
        $this->payment->setBaseAmountAuthorized($newBaseOrderedAmount);
        $this->payment->setAmountPaid($newBaseOrderedAmount);
        $this->payment->setBaseAmountPaidOnline($newOrderedAmount);
    }

    /**
     * Add transaction id to invoices for enable button "Refund Online"
     */
    private function updateInvoice()
    {
        $order = $this->getOrder();
        $payment = $order->getPayment();
        $lastTransId = $payment->getLastTransId();

        $invoices = $order->getInvoiceCollection()->getItems();
        foreach ($invoices as $invoice) {
            if ($invoice->getTransactionId() == null) {
                $invoice->setTransactionId($lastTransId)->save();
            }
        }
    }

    private function savePayment()
    {
        $this->payment->getOrder()->save();
        $this->orderPaymentRepository->save($this->payment);
    }

    public function fixRounding($price)
    {
        $decimal = '.';
        $splittedPrice = explode($decimal, $price);

        $firstDecimal  = null;
        $secondDecimal = null;
        if (isset($splittedPrice[1])) {
            $firstDecimal  = (isset($splittedPrice[1][0])) ? $splittedPrice[1][0] : null;
            $secondDecimal = (isset($splittedPrice[1][1])) ? $splittedPrice[1][1] : null;
        }

        $price = number_format(
            (float) $splittedPrice[0].$decimal.$firstDecimal.$secondDecimal,
            2
        );

        return $price;
    }
}
