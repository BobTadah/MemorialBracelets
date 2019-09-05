<?php

namespace IWD\AuthCIM\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Api\TransactionRepositoryInterface;

/**
 * Class FetchHandler
 * @package IWD\AuthCIM\Gateway\Response
 */
class FetchHandler implements HandlerInterface
{
    /**
     * @var ParseResponse
     */
    private $parseResponse;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var array
     */
    private $transactionInfoMap = [
        "submitTimeUTC",
        "submitTimeLocal",
        "transactionType",
        "transactionStatus",
        "responseCode",
        "responseReasonCode",
        "responseReasonDescription",
        "authCode",
        "AVSResponse",
        "cardCodeResponse",
        "authAmount",
        "settleAmount",
        "marketType"
    ];
    /*
     *
     */
    protected $orderRepository;

    /**
     * FetchHandler constructor.
     * @param ParseResponse $parseResponse
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        ParseResponse $parseResponse,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository

    ) {
        $this->parseResponse = $parseResponse;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param object order
     */
    public function getOrder($id)
    {
        return $this->orderRepository->get($id);
    }
    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($response['transaction'])) {
            throw new LocalizedException(__('Can not fetch transaction info'));
        }
        $transactionId = isset($handlingSubject['transactionId']) ? $handlingSubject['transactionId'] : null;

        $payment = $handlingSubject['payment'];
        $orderId = $payment->getOrder()->getId();
        $order = $this->getOrder($orderId);
        $paymentId = $order->getPayment()->getId();

        $transaction = $this->transactionRepository->getByTransactionId($transactionId, $paymentId, $orderId);

        $rawDetails = array_intersect_key($response['transaction'], array_flip($this->transactionInfoMap));

        $transaction->setAdditionalInformation(Transaction::RAW_DETAILS, $rawDetails);
        $this->transactionRepository->save($transaction);

    }

    /**
     * @return bool
     */
    public function isTransactionClosed()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isParentTransactionClosed()
    {
        return false;
    }
}
