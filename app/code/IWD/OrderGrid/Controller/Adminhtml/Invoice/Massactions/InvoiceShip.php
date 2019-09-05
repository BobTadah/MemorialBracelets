<?php

namespace IWD\OrderGrid\Controller\Adminhtml\Invoice\MassActions;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\ShipOrderInterface;
use IWD\OrderGrid\Controller\Adminhtml\AbstractMassAction;


class InvoiceShip extends AbstractMassAction
{
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
     * @var InvoiceOrderInterface
     */
    protected $invoiceOrder;

    /**
     * @var ShipOrderInterface
     */
    protected $shipOrder;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param InvoiceOrderInterface $invoiceOrder
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        InvoiceOrderInterface $invoiceOrder,
        ShipOrderInterface $shipOrder
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->invoiceOrder = $invoiceOrder;
        $this->shipOrder = $shipOrder;
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
        $countInvoiceOrder = 0;
        $countShipOrder = 0;
        $items = $collection->getItems();
        foreach ($items as $order) {
            try {
                $this->invoiceOrder->execute($order->getId());
                $countInvoiceOrder++;
            } catch (\Exception $e) {
                $message = '#%1 ' . $e->getMessage();
                $this->messageManager->addErrorMessage(__($message, $order->getIncrementId()));
            }

        }


        foreach ($items as $order) {
            try {
                $this->shipOrder->execute($order->getId());
                $countShipOrder++;
            } catch (\Exception $e) {
                $message = '#%1 ' . $e->getMessage();
                $this->messageManager->addErrorMessage(__($message, $order->getIncrementId()));
            }

        }


        $countNonInvoiceOrder = $collection->count() - $countInvoiceOrder;

        if ($countNonInvoiceOrder && $countInvoiceOrder) {
            $this->messageManager->addErrorMessage(__('%1 order(s) cannot be invoice.', $countNonInvoiceOrder));
        } elseif ($countNonInvoiceOrder) {
            $this->messageManager->addErrorMessage(__('You cannot invoice the order(s).'));
        }

        if ($countInvoiceOrder) {
            $this->messageManager->addSuccessMessage(__('We invoiced %1 order(s).', $countInvoiceOrder));
        }


        $countNonShipOrder = $collection->count() - $countShipOrder;

        if ($countNonShipOrder && $countShipOrder) {
            $this->messageManager->addErrorMessage(__('%1 order(s) cannot be ship.', $countNonShipOrder));
        } elseif ($countNonShipOrder) {
            $this->messageManager->addErrorMessage(__('You cannot ship the order(s).'));
        }

        if ($countShipOrder) {
            $this->messageManager->addSuccessMessage(__('We shipped %1 order(s).', $countShipOrder));
        }


        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }


    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('IWD_OrderGrid::iwdordergrid_invoice_create')
            && $this->_authorization->isAllowed('IWD_OrderGrid::iwdordergrid_ship')
            && $this->_authorization->isAllowed('Magento_Sales::invoice')
            && $this->_authorization->isAllowed('Magento_Sales::ship');
    }
}