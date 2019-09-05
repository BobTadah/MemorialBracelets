<?php

namespace IWD\OrderGrid\Controller\Adminhtml\Order\MassActions;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\ShipOrderInterface;
use IWD\OrderGrid\Controller\Adminhtml\AbstractMassAction;


class Ship extends AbstractMassAction
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
     * @var ShipOrderInterface
     */
    protected $shipOrder;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ShipOrderInterface $shipOrder
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ShipOrderInterface $shipOrder
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
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
        $countShipOrder = 0;
        foreach ($collection->getItems() as $order) {
            try {
                $this->shipOrder->execute($order->getId());
                $countShipOrder++;
            } catch (\Exception $e) {
                $message = '#%1 ' . $e->getMessage();
                $this->messageManager->addErrorMessage(__($message, $order->getIncrementId()));
            }

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
            && $this->_authorization->isAllowed('IWD_OrderGrid::iwdordergrid_ship');
    }
}