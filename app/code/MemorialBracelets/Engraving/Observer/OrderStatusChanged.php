<?php

namespace MemorialBracelets\Engraving\Observer;

use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use MemorialBracelets\Engraving\Model\Email\Container\EngravingIdentity;

class OrderStatusChanged implements ObserverInterface
{
    /** @var TransportBuilder */
    protected $transportBuilder;
    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    /** @var CustomerNameGenerationInterface */
    protected $nameGenerator;
    /** @var EngravingIdentity */
    protected $identity;
    /** @var ManagerInterface */
    protected $eventManager;
    /** @var PaymentHelper */
    protected $paymentHelper;
    /** @var Renderer */
    protected $addressRenderer;
    /** @var StoreRepositoryInterface */
    protected $storeRepository;
    /** @var OrderFactory */
    protected $orderFactory;
    /** @var OrderStatusHistoryRepositoryInterface */
    protected $historyRepository;
    /** @var SearchCriteriaFactory */
    protected $criteriaFactory;
    /** @var SortOrderFactory */
    protected $sortOrderFactory;
    /** @var FilterGroupFactory */
    protected $filterGroupFactory;
    /** @var FilterFactory */
    protected $filterFactory;
    /** @var CustomerFactory */
    protected $customerFactory;

    private $orders = [];

    /**
     * OrderStatusChanged constructor.
     * @param TransportBuilder                      $transportBuilder
     * @param ScopeConfigInterface                  $scopeConfig
     * @param CustomerNameGenerationInterface       $nameGenerator
     * @param EngravingIdentity                     $identity
     * @param ManagerInterface                      $eventManager
     * @param PaymentHelper                         $paymentHelper
     * @param Renderer                              $addressRenderer
     * @param StoreRepositoryInterface              $storeRepository
     * @param OrderFactory                          $orderFactory
     * @param OrderStatusHistoryRepositoryInterface $historyRepository
     * @param SearchCriteriaFactory                 $criteriaFactory
     * @param SortOrderFactory                      $sortOrderFactory
     * @param FilterGroupFactory                    $filterGroupFactory
     * @param FilterFactory                         $filterFactory
     * @param CustomerFactory                       $customerFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        CustomerNameGenerationInterface $nameGenerator,
        EngravingIdentity $identity,
        ManagerInterface $eventManager,
        PaymentHelper $paymentHelper,
        Renderer $addressRenderer,
        StoreRepositoryInterface $storeRepository,
        OrderFactory $orderFactory,
        OrderStatusHistoryRepositoryInterface $historyRepository,
        SearchCriteriaFactory $criteriaFactory,
        SortOrderFactory $sortOrderFactory,
        FilterGroupFactory $filterGroupFactory,
        FilterFactory $filterFactory,
        CustomerFactory $customerFactory
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->nameGenerator = $nameGenerator;
        $this->identity = $identity;
        $this->eventManager = $eventManager;
        $this->paymentHelper = $paymentHelper;
        $this->addressRenderer = $addressRenderer;
        $this->storeRepository = $storeRepository;
        $this->orderFactory = $orderFactory;
        $this->historyRepository = $historyRepository;
        $this->criteriaFactory = $criteriaFactory;
        $this->sortOrderFactory = $sortOrderFactory;
        $this->filterGroupFactory = $filterGroupFactory;
        $this->filterFactory = $filterFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->identity->isEnabled()) {
            return;
        }

        $event = $observer->getEvent();
        /** @var OrderInterface $order */
        $order = $event->getData('order');

        /** @var OrderStatusHistoryRepositoryInterface $historyRepository */
        $historyRepository = $this->historyRepository;
        /** @var SearchCriteria $search */
        $search = $this->criteriaFactory->create();
        /** @var SortOrder $sort */
        $sort = $this->sortOrderFactory->create();
        /** @var FilterGroup $filterGroup */
        $filterGroup = $this->filterGroupFactory->create();
        /** @var Filter $filter */
        $filter = $this->filterFactory->create();

        $sort->setField(OrderStatusHistoryInterface::ENTITY_ID);
        $sort->setDirection(SortOrder::SORT_DESC);
        $search->setSortOrders([$sort]);

        $filter->setField(OrderStatusHistoryInterface::PARENT_ID);
        $filter->setValue($order->getEntityId());
        $filterGroup->setFilters([$filter]);
        $search->setFilterGroups([$filterGroup]);

        $histories = $historyRepository->getList($search)->getItems();
        // First history is the most recent
        reset($histories);
        $history = current($histories);
        $originalStatus = $history ? $history->getStatus() : false;
        $newStatus = $order->getStatus();

        if ($originalStatus != $newStatus && $newStatus == 'engraving') {
            $this->sendEmail($order);
            $this->sendCopyTo($order);
        }
    }

    private function sendEmail(OrderInterface $order)
    {
        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $this->customerFactory->create();
        $customer->setData(CustomerInterface::FIRSTNAME, $order->getCustomerFirstname());
        $customer->setData(CustomerInterface::LASTNAME, $order->getCustomerLastname());
        $customer->setData(CustomerInterface::PREFIX, $order->getCustomerPrefix());
        $customer->setData(CustomerInterface::MIDDLENAME, $order->getCustomerMiddlename());
        $customer->setData(CustomerInterface::SUFFIX, $order->getCustomerSuffix());

        $copyTo = $this->identity->getEmailCopyTo();

        $transportBuilder = $this->prepareEmail($order)
            ->addTo($order->getCustomerEmail(), $this->nameGenerator->getCustomerName($customer));

        if (!empty($copyTo) && $this->identity->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $transportBuilder->addBcc($email);
            }
        }

        $transportBuilder->getTransport()->sendMessage();
    }

    private function sendCopyTo(OrderInterface $order)
    {
        $copyTo = $this->identity->getEmailCopyTo();

        if (!empty($copyTo) && $this->identity->getCopyMethod() == 'copy') {
            foreach ($copyTo as $email) {
                $transportBuilder = $this->prepareEmail($order)
                    ->addTo($email);

                $transportBuilder->getTransport()->sendMessage();
            }
        }
    }

    /**
     * @param OrderInterface $order
     * @return TransportBuilder
     */
    private function prepareEmail(OrderInterface $order)
    {
        return $this->transportBuilder
            ->setTemplateIdentifier($this->identity->getTemplateId())
            ->setTemplateOptions([
                'area'  => Area::AREA_FRONTEND,
                'store' => $order->getStoreId(),
            ])
            ->setTemplateVars($this->getTemplateVars($order))
            ->setFrom($this->identity->getEmailIdentity());
    }

    private function getTemplateVars(OrderInterface $order)
    {
        $store = $this->storeRepository->getById($order->getStoreId());
        $transport = [
            'order'                    => $order,
            'billing'                  => $order->getBillingAddress(),
            'payment_html'             => $this->getPaymentHtml($order),
            'store'                    => $store,
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress'  => $this->getFormattedBillingAddress($order),
        ];

        $this->eventManager->dispatch(
            'email_engraving_set_template_vars_before',
            ['transport' => $transport]
        );

        return $transport;
    }

    /**
     * Get payment info block as html
     *
     * @param OrderInterface $order
     * @return string
     */
    protected function getPaymentHtml(OrderInterface $order)
    {
        $order = $this->getOrderFromInterface($order);

        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identity->getStore()->getStoreId()
        );
    }

    /**
     * @param OrderInterface $order
     * @return string|null
     */
    protected function getFormattedShippingAddress(OrderInterface $order)
    {
        $order = $this->getOrderFromInterface($order);
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param OrderInterface $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        $order = $this->getOrderFromInterface($order);
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /**
     * Return an Order object from an OrderInterface
     *
     * @param OrderInterface $order
     * @return \Magento\Sales\Model\Order
     */
    private function getOrderFromInterface(OrderInterface $order)
    {
        if (!isset($this->orders[$order->getEntityId()])) {
            $realOrder = $this->orderFactory->create()
                ->loadByIncrementIdAndStoreId($order->getIncrementId(), $order->getStoreId());
            $this->orders[$order->getEntityId()] = $realOrder;
        }
        return $this->orders[$order->getEntityId()];
    }
}
