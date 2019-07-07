<?php

namespace IWD\OrderGrid\Controller\Adminhtml\Order\MassActions;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use IWD\OrderGrid\Controller\Adminhtml\AbstractMassAction;
use Magento\Sales\Api\OrderManagementInterface;


class Comment extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_OrderGrid::iwdordergrid_order_comment';

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
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    protected $orderStatusHistory;


    /**
     * Comment constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderStatusHistoryInterfaceFactory $orderStatusHistory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        OrderStatusHistoryInterfaceFactory $orderStatusHistory
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->orderStatusHistory = $orderStatusHistory;
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

        $comment = $this->getRequest()->getParam('iwd_comment', null);
        if(!isset($comment) || empty($comment) ){
            $this->messageManager->addErrorMessage(__('Not found comment'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->getComponentRefererUrl());
            return $resultRedirect;
        }




        $countComment = 0;
        foreach ($collection->getItems() as $order) {
            try {
                $statusHistory = $this->orderStatusHistory->create()
                    ->setComment($comment)
                    ->setIsCustomerNotified(1)
                    ->setStatus($order->getStatus())
                    ->setEntityName('order');
                $this->orderManagement->addComment($order->getId(), $statusHistory);
                $countComment++;
            } catch (\Exception $e) {
                $message = '#%1 ' . $e->getMessage();
                $this->messageManager->addErrorMessage(__($message, $order->getIncrementId()));
            }

        }
        $countNonComment = $collection->count() - $countComment;

        if ($countNonComment && $countComment) {
            $this->messageManager->addErrorMessage(__('%1 order(s) cannot be comment.', $countNonComment));
        } elseif ($countNonComment) {
            $this->messageManager->addErrorMessage(__('You cannot comment the order(s).'));
        }

        if ($countComment) {
            $this->messageManager->addSuccessMessage(__('We commented %1 order(s).', $countComment));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}