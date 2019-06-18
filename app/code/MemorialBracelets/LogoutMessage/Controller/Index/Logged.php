<?php

namespace MemorialBracelets\LogoutMessage\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Logged
 * @package MemorialBracelets\LogoutMessage\Controller\Index
 */
class Logged extends Action
{
    /**
     * @var Session
     */
    private $session;

    /**
     * Logout constructor.
     * @param Context $context
     * @param Session $session
     */
    public function __construct(
        Context $context,
        Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            $this->messageManager->addNoticeMessage(
                __('You have been logged out due to inactivity. Please refresh the page and log back in.')
            );
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setData($this->session->isLoggedIn());
    }
}
