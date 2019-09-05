<?php

namespace IWD\OrderGrid\Model\Order;

use Magento\Framework\Exception\LocalizedException;

class Order extends \Magento\Sales\Model\Order
{

    /**
     * @var float
     */
    protected $oldTotal;

    /**
     * @var float
     */
    protected $oldQtyOrdered;


    /**
     * @var \IWD\OrderGrid\Model\Invoice\Invoice
     */
    protected $invoice;

    /**
     * @var \IWD\OrderGrid\Model\Creditmemo\Creditmemo
     */
    protected $creditmemo;

    /**
     * @var \IWD\OrderGrid\Model\Shipment\Shipment
     */
    protected $shipment;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * @var \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory
     */
    protected $taxCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory
     */
    protected $orderHistoryCollectionFactory;



    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory
     * @param \IWD\OrderGrid\Model\Invoice\Invoice $invoice
     * @param \IWD\OrderGrid\Model\Shipment\Shipment $shipment
     * @param \IWD\OrderGrid\Model\Creditmemo\Creditmemo $creditmemo
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $orderHistoryCollectionFactory
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $taxCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory,
        \IWD\OrderGrid\Model\Invoice\Invoice $invoice,
        \IWD\OrderGrid\Model\Shipment\Shipment $shipment,
        \IWD\OrderGrid\Model\Creditmemo\Creditmemo $creditmemo,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $orderHistoryCollectionFactory,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $taxCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->invoice = $invoice;
        $this->creditmemo = $creditmemo;
        $this->shipment = $shipment;
        $this->_scopeConfig = $scopeConfig;
        $this->taxCollectionFactory = $taxCollectionFactory;
        $this->orderHistoryCollectionFactory = $orderHistoryCollectionFactory;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $timezone,
            $storeManager,
            $orderConfig,
            $productRepository,
            $orderItemCollectionFactory,
            $productVisibility,
            $invoiceManagement,
            $currencyFactory,
            $eavConfig,
            $orderHistoryFactory,
            $addressCollectionFactory,
            $paymentCollectionFactory,
            $historyCollectionFactory,
            $invoiceCollectionFactory,
            $shipmentCollectionFactory,
            $memoCollectionFactory,
            $trackCollectionFactory,
            $salesOrderCollectionFactory,
            $priceCurrency,
            $productListFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    protected function prepareParamsForEditItems($params)
    {
        if (!isset($params['order_id']) || !isset($params['item'])) {
            throw new LocalizedException(__('Incorrect params for edit order items'));
        }

        $this->load($params['order_id']);
        $this->newParams = $params['item'];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function checkStatus()
    {
        if (!$this->isAllowEditOrder()) {
            throw new LocalizedException(__("You can't edit order with current status."));
        }
    }


    /**
     * @return bool
     */
    public function isAllowDeleteOrder()
    {
        $isAllowedDelete = $this->_scopeConfig->getValue('iwd_ordergrid/allow_delete/orders');

        if ($isAllowedDelete) {
            $allowedStatuses = $this->_scopeConfig->getValue('iwd_ordergrid/allow_delete/order_statuses');
            $allowedStatuses = explode(',', $allowedStatuses);
            return in_array($this->getStatus(), $allowedStatuses);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        $this->deleteRelatedShipments();
        $this->deleteRelatedInvoices();
        $this->deleteRelatedCreditMemos();
        $this->deleteRelatedOrderInfo();

        return parent::beforeDelete();
    }

    /**
     * @return void
     */
    protected function deleteRelatedOrderInfo()
    {
        try {
            $collection = $this->_addressCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->_orderItemCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->_paymentCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->orderHistoryCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->taxCollectionFactory->create()->loadByOrder($this);
            foreach ($collection as $object) {
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedInvoices()
    {
        try {
            $collection = $this->getInvoiceCollection();
            foreach ($collection as $item) {
                $object = $this->invoice->load($item->getId());
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedShipments()
    {
        try {
            $collection = $this->getShipmentsCollection();
            foreach ($collection as $item) {
                $object = $this->shipment->load($item->getId());
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedCreditMemos()
    {
        try {
            $collection = $this->getCreditmemosCollection();
            foreach ($collection as $item) {
                $object = $this->creditmemo->load($item->getId());
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @param string $status
     * @return void
     */
    public function updateOrderStatus($status)
    {
        //$oldStatus = $this->getStatus();
       // $newStatus = $status;

        $this->setData('status', $status)->save();


    }

}