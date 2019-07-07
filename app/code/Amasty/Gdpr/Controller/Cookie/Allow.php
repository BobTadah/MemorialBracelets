<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Cookie;

use Amasty\Gdpr\Model\CookieManager;
use Magento\Framework\App\Action\Context;

class Allow extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    public function __construct(
        Context $context,
        CookieManager $cookieManager
    ) {
        parent::__construct($context);
        $this->cookieManager = $cookieManager;
    }

    public function execute()
    {
        $this->cookieManager->setIsAllowCookies(true);
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
