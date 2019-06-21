<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Amasty\Gdpr\Model\ResourceModel\Policy\Collection;
use Amasty\Gdpr\Model\ResourceModel\Policy\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractPolicy
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $policyCollectionFactory;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;


    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $policyCollectionFactory,
        PolicyRepositoryInterface $policyRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->policyCollectionFactory = $policyCollectionFactory;
        $this->policyRepository = $policyRepository;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->policyCollectionFactory->create());

        if ($collection->count() > 0) {
            try {
                foreach ($collection->getItems() as $policy) {
                    $this->policyRepository->delete($policy);
                }

                $this->messageManager->addSuccessMessage(
                    __('%1 policies(s) has been successfully deleted', $collection->count())
                );

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred'));
                $this->logger->critical($e);
            }
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
