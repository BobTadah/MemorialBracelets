<?php

namespace MemorialBracelets\NameProductRequest\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /** Enable Name Product Request module */
    const CONFIG_ENABLE = 'catalog/nameproduct_request/enable';

    /** Simple products to connect to name products requested through the module */
    const CONFIG_PRODUCT_LINKS = 'catalog/nameproduct_request/simples';

    /** Email address admin notification emails should come from */
    const CONFIG_EMAIL_FROM = 'catalog/nameproduct_request/email_from';

    /** Email address admin notifications emails should send to */
    const CONFIG_EMAIL_TO = 'catalog/nameproduct_request/email_to';

    /** Email addresses that should be copied on admin notification emails*/
    const CONFIG_EMAIL_CC = 'catalog/nameproduct_request/email_cc';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * If the module is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return !!$this->scopeConfig->isSetFlag(static::CONFIG_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * SKUs of products to attach to requested name products
     * @return string[]
     */
    public function getProductLinks()
    {
        $value = $this->scopeConfig->getValue(static::CONFIG_PRODUCT_LINKS);

        return empty($value) ? [] : explode(',', $value);
    }

    /**
     * Get address to send emails to admins from
     * @return mixed
     */
    public function getEmailFrom()
    {
        return $this->scopeConfig->getValue(static::CONFIG_EMAIL_FROM, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get address to send emails to admins to
     * @return mixed
     */
    public function getEmailTo()
    {
        return $this->scopeConfig->getValue(static::CONFIG_EMAIL_TO, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get addresses to carbon-copy on emails
     * @return array
     */
    public function getEmailCc()
    {
        $value = $this->scopeConfig->getValue(static::CONFIG_EMAIL_CC, ScopeInterface::SCOPE_STORE);
        return empty($value) ? [] : explode(',', $value);
    }
}
