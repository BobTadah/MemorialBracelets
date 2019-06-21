<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\DeleteRequest;
use Amasty\Gdpr\Model\DeleteRequestFactory;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\Authentication;

class AddDeleteRequest extends AbstractAccountAction
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $deleteRequestRepository;

    /**
     * @var DeleteRequestFactory
     */
    private $deleteRequestFactory;

    /**
     * @var ActionLogger
     */
    private $actionLogger;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Authentication
     */
    private $authentication;

    public function __construct(
        Context $context,
        Session $customerSession,
        LoggerInterface $logger,
        DeleteRequestFactory $deleteRequestFactory,
        DeleteRequestRepositoryInterface $deleteRequestRepository,
        ActionLogger $actionLogger,
        FormKeyValidator $formKeyValidator,
        Authentication $authentication
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->deleteRequestRepository = $deleteRequestRepository;
        $this->deleteRequestFactory = $deleteRequestFactory;
        $this->actionLogger = $actionLogger;
        $this->formKeyValidator = $formKeyValidator;
        $this->authentication = $authentication;
    }

    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page.'));
            $this->_redirect('*/*/settings');
            return;
        }

        $customerId = $this->customerSession->getCustomerId();
        $customerPass = $this->getRequest()->getParam('current_password');

        try {
            $this->authentication->authenticate($customerId, $customerPass);
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addErrorMessage(__('Wrong Password. Please recheck it.'));
            $this->_redirect('*/*/settings');
            return;
        }

        try {
            /** @var DeleteRequest $request */
            $request = $this->deleteRequestFactory->create();
            $request->setCustomerId($this->customerSession->getId());
            $this->deleteRequestRepository->save($request);
            $this->actionLogger->logAction('delete_request_submitted', $request->getCustomerId());
            $this->messageManager->addSuccessMessage(
                __('Thank you, your account delete request was recorded.')
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($exception);
        }

        $this->_redirect('*/*/settings');
    }
}
