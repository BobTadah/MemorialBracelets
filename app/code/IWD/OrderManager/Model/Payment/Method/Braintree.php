<?php

namespace IWD\OrderManager\Model\Payment\Method;

use Magento\Sales\Api\OrderPaymentRepositoryInterface as OrderPaymentRepository;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use IWD\OrderManager\Helper\Data;

/**
 * Class Braintree
 * @package IWD\OrderManager\Model\Payment\Method
 */
class Braintree extends AbstractMethod
{
    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    private $payment;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    private $vaultPayment;

    private $braintreeAdapter;

    /**
     * @var Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var Data
     */
    private $configHelper;


    /**
     * Braintree constructor.
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param VaultPaymentInterface $vaultPayment
     * @param BraintreeAdapter $braintreeAdapter
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Data $configHelper,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        PaymentTokenManagementInterface $paymentTokenManagement,
        BraintreeAdapterFactory $braintreeAdapter,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        OrderPaymentRepository $orderPaymentRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->configHelper = $configHelper;
        $this->transactionBuilder = $transactionBuilder;
        $this->braintreeAdapter = $braintreeAdapter;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->paymentTokenManagement = $paymentTokenManagement;

    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function reauthorize()
    {
        if ($this->configHelper->checkBraintreeReauthorization()) {
            $order = $this->getOrder();
            $this->payment = $order->getPayment();

            $paymentToken = $this->paymentTokenManagement->getByPaymentId($this->payment->getEntityId());
            $token = $paymentToken->getGatewayToken();
            $newOrderedAmount = $order->getBaseGrandTotal();
            $authorizedAmount = $this->payment->getBaseAmountAuthorized();

            /**
             * @var $paymentBraintree \Magento\Sales\Model\Order\Payment
             */

            $newTotal = $newOrderedAmount;//$order->getCurrentOrderTotal();
            $oldTotal = $authorizedAmount;//$order->getOldTotal();

            /**
             * Authorized (but do not captured) less then we need now.
             * Ex. (authorized $80 but we need $100 - so AUTHORIZE +$20)
             */
            if (!$order->hasInvoices() && $authorizedAmount < $newOrderedAmount) {

                $orderAmount = $newOrderedAmount - $authorizedAmount;
                ///$paymentBraintree->authorize($this->payment, $amount);
                $transactionResult = $this->braintreeAdapter->create()->sale(
                    [
                        'paymentMethodToken' => $token,
                        'amount' => $orderAmount,
                        'orderId' =>$order->getIncrementId()
                    ]
                );
                if (!$transactionResult instanceof \Braintree\Result\Successful) {
                    throw new LocalizedException(__('Error in re-authorizing payment.'));
                }


                $this->saveTransaction($order, $transactionResult->transaction, $orderAmount);


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

                if ($amount > 0) {
                    $this->payment->setBaseAmountAuthorized($newOrderedAmount);
                    $this->capture($amount);
                }
                return true;
            }
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
        $newBaseOrderedAmount = $order->getBaseGrandTotal();
        $newOrderedAmount = $order->getGrandTotal();

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

    private function saveTransaction($order = null, $paymentData = array(), $orderAmount)
    {
        try {
            // Prepare payment object
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData->id);
            $payment->setTransactionId($paymentData->id);
            $payment->setIsTransactionClosed(0);
            $additionalInfo = ['method'=>'brainetree', 'amount'=>$orderAmount];
            $payment->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $additionalInfo]);

            // Formatted price
            $formatedPrice = $order->getBaseCurrency()->formatTxt($orderAmount);

            // Prepare transaction
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData->id)
                ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) (array) $additionalInfo])
                ->setFailSafe(true)
                ->build(Transaction::TYPE_AUTH);

            // Add transaction to payment
            $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
            $payment->setParentTransactionId(null);

            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();

            return  $transaction->getTransactionId();

        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }



    }
}