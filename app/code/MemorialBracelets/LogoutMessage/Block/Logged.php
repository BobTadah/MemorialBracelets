<?php

namespace MemorialBracelets\LogoutMessage\Block;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Logout
 * @package MemorialBracelets\LogoutMessage\Block
 */
class Logged extends Template
{

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * Logout constructor.
     * @param SessionManagerInterface $sessionManager
     * @param Context $context
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        Context $context
    ) {
        parent::__construct($context);
        $this->sessionManager = $sessionManager;
    }

    /**
     * @return int
     */
    public function getCookieLifetime()
    {
        return $this->sessionManager->getCookieLifetime();
    }
}
