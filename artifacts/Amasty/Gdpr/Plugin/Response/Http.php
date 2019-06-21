<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Plugin\Response;

use Amasty\Gdpr\Model\Config\Source\CookiePolicyBar;

class Http
{
    /**
     * @var \Amasty\Gdpr\Model\Config
     */
    private $configProvider;

    /**
     * @var \Amasty\Gdpr\Model\CookieManager
     */
    private $cookieManager;

    public function __construct(
        \Amasty\Gdpr\Model\Config $configProvider,
        \Amasty\Gdpr\Model\CookieManager $cookieManager
    ) {
        $this->configProvider = $configProvider;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @param \Magento\Framework\App\Response\Http $subject
     */
    public function beforeSendResponse(\Magento\Framework\App\Response\Http $subject)
    {
        if ($this->configProvider->getCookiePrivacyBar() === CookiePolicyBar::CONFIRMATION
            && $this->cookieManager->isAllowCookies() === false) {
            $this->cookieManager->deleteCookies($this->configProvider->getConfirmationCookies());
        }
    }
}
