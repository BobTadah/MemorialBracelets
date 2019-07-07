<?php

namespace IWD\SalesRep\Controller\Adminhtml\User;

use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Commission
 * @package IWD\SalesRep\Controller\Adminhtml\User
 */
class Commission extends \IWD\SalesRep\Controller\Adminhtml\AbstractUser
{
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Commission constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection,
        LayoutFactory $layoutFactory,
        RawFactory $resultRawFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context, $registry, $customerFactory, $attachedCustomerCollection);
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $salesrepId = $this->_request->getParam('salesrep_id');
        $customerId = $this->_request->getParam('customer_id');

        $attachedCustomer = $this->attachedCustomerCollectionFactory->create()
            ->addFieldToFilter('salesrep_id', $salesrepId)
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();

        if ($attachedCustomer->isEmpty()) {
            $res = [
                'html' => 'Please, assign customer to Sales Rep first',
                'res' => false,
            ];
        } else {
            // This hack need for proper init Ui Form
            $params = $this->_request->getParams();
            $params['entity_id'] = $attachedCustomer->getId();
            $this->_request->setParams($params);

            $result = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            $res = [
                'res' => true,
                'html' => $result->getLayout()->renderElement('salesrep_user_commission_form', false)
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents(json_encode($res));
        return $resultRaw;
    }
}
