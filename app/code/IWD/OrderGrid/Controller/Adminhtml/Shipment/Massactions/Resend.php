<?php

namespace IWD\OrderGrid\Controller\Adminhtml\Shipment\MassActions;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\ShipmentManagementInterface;
use IWD\OrderGrid\Controller\Adminhtml\AbstractMassAction;
use Magento\Sales\Api\ShipmentRepositoryInterface;
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
     * @var ShipmentManagementInterface
     */
    protected $shipmentManagement;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;


    /**
     * Resend constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ShipmentManagementInterface $shipmentManagement
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ShipmentManagementInterface $shipmentManagement,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->shipmentManagement = $shipmentManagement;
        $this->shipmentRepository = $shipmentRepository;
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
        $countSendShipments = 0;

        foreach ($collection->getItems() as $order) {
            try {
                $hasShip = $order->hasShipments();
                if(!$hasShip){
                    $this->messageManager->addErrorMessage(__('The order #%1 doesn\'t have shipments', $order->getIncrementId()));
                    continue;
                }

                $items = $this->getItemsById($order->getId());
                foreach ($items as $item) {
                    $result = $this->shipmentManagement->notify($item->getId());
                    if ($result) {
                        $countSendShipments++;
                    }
                }

            } catch (\Exception $e) {
                $message = '#%1 ' . $e->getMessage();
                $this->messageManager->addErrorMessage(__($message, $order->getIncrementId()));
            }

        }
        $countNonSendShipments = $collection->count() - $countSendShipments;

        if ($countNonSendShipments && $countSendShipments) {
            $this->messageManager->addErrorMessage(__('%1 shipment(s) cannot be send.', $countNonSendShipments));
        } elseif ($countNonSendShipments) {
            $this->messageManager->addErrorMessage(__('You cannot send shipment(s).'));
        }

        if ($countSendShipments) {
            $this->messageManager->addSuccessMessage(__('We sent %1 shipment(s).', $countSendShipments));
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

        return $this->shipmentRepository->getList($searchCriteria);
    }
}