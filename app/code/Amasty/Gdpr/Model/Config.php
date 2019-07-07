<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const PATH_PREFIX = 'amasty_gdpr';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const COOKIE_POLICY_BAR = 'cookie_policy/bar';

    const NOTIFY_BAR_TEXT = 'cookie_policy/notify_bar_text';

    const CONFIRMATION_BAR_TEXT = 'cookie_policy/confirmation_bar_text';

    const CONFIRMATION_COOKIES = 'cookie_policy/confirmation_cookies';

    const BACKGROUND_BAR_COLLOR = 'cookie_bar_customisation/background_color_cookies';

    const BUTTONS_BAR_COLLOR = 'cookie_bar_customisation/buttons_color_cookies';

    const TEXT_BAR_COLLOR = 'cookie_bar_customisation/text_color_cookies';

    const LINK_BAR_COLLOR = 'cookie_bar_customisation/link_color_cookies';

    const BUTTONS_TEXT_BAR_COLLOR = 'cookie_bar_customisation/buttons_text_color_cookies';

    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getEEACountryCodes()
    {
        $codes = explode(',', $this->getValue('eea_countries'));

        return $codes;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @param string $path
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return bool
     */
    public function isSetFlag($path, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::PATH_PREFIX . '/' . $path, $scopeType, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getNotifyBarText($scopeCode = null)
    {
        return $this->getValue(self::NOTIFY_BAR_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getConfirmationBarText($scopeCode = null)
    {
        return $this->getValue(self::CONFIRMATION_BAR_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return int
     */
    public function getCookiePrivacyBar($scopeCode = null)
    {
        return (int)$this->getValue(self::COOKIE_POLICY_BAR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return array
     */
    public function getConfirmationCookies($scopeCode = null)
    {
        $confirmationCookies = [];
        if ($cookies = $this->getValue(self::CONFIRMATION_COOKIES, $scopeCode)) {
            $confirmationCookies = preg_split('/\n|\r\n?/', $cookies);
        }
        return $confirmationCookies;
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getBackgroundBarCollor($scopeCode = null)
    {
        return $this->getValue(self::BACKGROUND_BAR_COLLOR, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getButtonsBarCollor($scopeCode = null)
    {
        return $this->getValue(self::BUTTONS_BAR_COLLOR, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getTextBarCollor($scopeCode = null)
    {
        return $this->getValue(self::TEXT_BAR_COLLOR, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getLinksBarCollor($scopeCode = null)
    {
        return $this->getValue(self::LINK_BAR_COLLOR, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getButtonTextBarCollor($scopeCode = null)
    {
        return $this->getValue(self::BUTTONS_TEXT_BAR_COLLOR, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }
}
