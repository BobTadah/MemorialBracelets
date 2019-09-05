<?php

namespace IWD\OrderGrid\Controller\Adminhtml\Invoice\MassActions;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\InvoiceManagementInterface;
use IWD\OrderGrid\Controller\Adminhtml\AbstractMassAction;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;


class Resend extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_OrderGrid::iwdordergrid_invoice_resend';

    /**
     * @var string
     */
    protected $redirectUrl = 'sales/order/index';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var object
     */
    protected $collectionFactory;


    /**
     * @var InvoiceManagementInterface
     */
    protected $invoiceManagement;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Resend constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param InvoiceManagementInterface $invoiceManagement
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        InvoiceManagementInterface $invoiceManagement,
        InvoiceRepositoryInterface $invoiceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->invoiceManagement = $invoiceManagement;
        $this->invoiceRepository = $invoiceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Return component referrer url
     * @return null|string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: $this->redirectUrl;
    }


    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countSendInvoice = 0;
        foreach ($collection->getItems() as $order) {
            try {
                $hasInvoice = $order->hasInvoices();
                if (!$hasInvoice) {
                    $this->messageManager->addErrorMessage(__('The order #%1 doesn\'t have invoices', $order->getIncrementId()));
                    continue;
                }


                $items = $this->getItemsById($order->getId());
                foreach ($items as $item) {
                    $result = $this->invoiceManagement->notify($item->getId());
                    if ($result) {
                        $countSendInvoice++;
                    }
                }


            } catch (\Exception $e) {
                $message = '#%1 ' . $e->getMessage();
                $this->messageManager->addErrorMessage(__($message, $order->getIncrementId()));
            }

        }
        $countNonSendInvoice = $collection->count() - $countSendInvoice;

        if ($countNonSendInvoice && $countSendInvoice) {
            $this->messageManager->addErrorMessage(__('%1 invoice(s) cannot be send.', $countNonSendInvoice));
        } elseif ($countNonSendInvoice) {
            $this->messageManager->addErrorMessage(__('You cannot send invoice(s).'));
        }

        if ($countSendInvoice) {
            $this->messageManager->addSuccessMessage(__('We sent %1 invoice(s).', $countSendInvoice));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    protected function getItemsById($id)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $id)
            ->create();

        return $this->invoiceRepository->getList($searchCriteria);
    }
}