<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Request;

use Amasty\Gdpr\Controller\Adminhtml\AbstractRequest;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

abstract class RequestProcessAction extends AbstractRequest
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Action\Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    protected function processRequests(Collection $requests, \Closure $action)
    {
        $customerIds = array_unique($requests->getColumnValues('customer_id'));

        foreach ($customerIds as $customerId) {
            try {
                $action($customerId);
                $requests->deleteByCustomerId($customerId);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('An error has occurred. Please check logs for more details'));
            }
        }

        return count($customerIds);
    }
}
