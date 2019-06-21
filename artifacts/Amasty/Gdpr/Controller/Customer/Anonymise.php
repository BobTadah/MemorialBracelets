<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Model\Anonymizer;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\Authentication;

class Anonymise extends AbstractAccountAction
{
    /**
     * @var Anonymizer
     */
    private $anonymizer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
        Anonymizer $anonymizer,
        Session $customerSession,
        LoggerInterface $logger,
        FormKeyValidator $formKeyValidator,
        Authentication $authentication
    ) {
        parent::__construct($context);
        $this->anonymizer = $anonymizer;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
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
            $this->anonymizer->anonymizeCustomer($this->customerSession->getCustomerId());
            $this->messageManager->addSuccessMessage(__('Anonymisation was successful'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($exception);
        }

        $this->_redirect('*/*/settings');
    }
}
