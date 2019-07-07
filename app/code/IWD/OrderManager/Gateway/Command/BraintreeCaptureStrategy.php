<?php
namespace IWD\OrderManager\Gateway\Command;

use Braintree\Transaction;
use IWD\OrderManager\Model\Adapter\BraintreeAdapterFactory;
use Magento\Braintree\Model\Adapter\BraintreeSearchAdapter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use IWD\OrderManager\Helper\Data;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CaptureStrategyCommand
 * @SuppressWarnings(PHPMD)
 */
class BraintreeCaptureStrategy implements CommandInterface
{
    /**
     * Braintree authorize and capture command
     */
    const SALE = 'sale';

    /**
     * Braintree capture command
     */
    const CAPTURE = 'settlement';

    /**
     * Braintree vault capture command
     */
    const VAULT_CAPTURE = 'vault_capture';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var BraintreeAdapterFactory
     */
    private $braintreeAdapterFactory;

    /**
     * @var BraintreeSearchAdapter
     */
    private $braintreeSearchAdapter;
    /**
     * @var Data
     */
    private $configHelper;

/**
     * @var ObjectManagerInterface 
     */
    private $objectManager;

    /**
     * BraintreeCaptureStrategy constructor.
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BraintreeAdapterFactory $braintreeAdapterFactory
     * @param BraintreeSearchAdapter $braintreeSearchAdapter
     * @param Data $configHelper
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BraintreeAdapterFactory $braintreeAdapterFactory,
        BraintreeSearchAdapter $braintreeSearchAdapter,
        Data $configHelper,
        ObjectManagerInterface $objectManager

    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->configHelper = $configHelper;
        //old braintree support
        $this->objectManager = $objectManager;
        if ($this->configHelper->isOldBraintree()) {
            $this->subjectReader = $this->objectManager->create("\Magento\Braintree\Gateway\Helper\SubjectReader");
        } else {
            $this->subjectReader = $this->objectManager->create("\Magento\Braintree\Gateway\SubjectReader");
        }


        $this->braintreeAdapterFactory = $braintreeAdapterFactory;
        $this->braintreeSearchAdapter = $braintreeSearchAdapter;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        $command = $this->getCommand($paymentDO);
        $this->commandPool->get($command)->execute($commandSubject);
        return $command;
    }

    /**
     * Gets command name.
     *
     * @param PaymentDataObjectInterface $paymentDO
     * @return string
     */
    private function getCommand(PaymentDataObjectInterface $paymentDO)
    {
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        // if auth transaction does not exist then execute authorize&capture command
        $existsCapture = $this->isExistsCaptureTransaction($payment);
        if (!$payment->getAuthorizationTransaction() && !$existsCapture) {
            return self::SALE;
        }

        // do capture for authorization transaction
        if (!$existsCapture && !$this->isExpiredAuthorization($payment, $paymentDO->getOrder())) {
            return self::CAPTURE;
        }

        // process capture for payment via Vault
        return self::VAULT_CAPTURE;
    }

    /**
     * Checks if authorization transaction does not expired yet.
     *
     * @param OrderPaymentInterface $payment
     * @param OrderAdapterInterface $orderAdapter
     * @return bool
     */
    private function isExpiredAuthorization(OrderPaymentInterface $payment, OrderAdapterInterface $orderAdapter)
    {
        $adapter = $this->braintreeAdapterFactory->create($payment->getOrder()->getStoreId());
        $collection = $adapter->search(
            [
                $this->braintreeSearchAdapter->id()->is($payment->getLastTransId()),
                $this->braintreeSearchAdapter->status()->is(Transaction::AUTHORIZATION_EXPIRED)
            ]
        );

        return $collection->maximumCount() > 0;
    }
    /**
     * Check if capture transaction already exists
     *
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );
        $transId = $payment->getCcTransId();
        if( isset($transId) && $this->configHelper->checkBraintreeReauthorization() ){
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder
                        ->setField('txn_id')
                        ->setValue($transId)
                        ->create(),
                ]
            );
        }


        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        return (boolean) $count;
    }
}