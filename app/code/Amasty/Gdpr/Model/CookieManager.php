<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CookieManager
{
    /**#@+*/
    const ALLOW_COOKIES = 'am_allow_cookies';
    /**#@-*/

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param bool $status
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setIsAllowCookies($status = true)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath('/')
            ->setDurationOneYear();

        try {
            $this->cookieManager->setPublicCookie(self::ALLOW_COOKIES, (int)$status, $cookieMetadata);
        } catch (\Exception $e) {
            null;
        }
    }

    /**
     * @return bool
     */
    public function isAllowCookies()
    {
        return (bool)$this->cookieManager->getCookie(self::ALLOW_COOKIES);
    }

    public function deleteCookies($cookies)
    {
        try {
            foreach ($cookies as $cookie) {
                $this->cookieManager->deleteCookie($cookie);
            }
        } catch (\Exception $e) {
            null;
        }
    }
}
