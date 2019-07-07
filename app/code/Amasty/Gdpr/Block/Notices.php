<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Config\Source\CookiePolicyBar;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\Filter as CmsTemplateFilter;

class Notices extends Template
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CmsTemplateFilter
     */
    private $cmsTemplateFilter;

    public function __construct(
        Config $configProvider,
        Template\Context $context,
        CmsTemplateFilter $cmsTemplateFilter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->cmsTemplateFilter = $cmsTemplateFilter;
    }

    /**
     * @return string
     */
    public function getCookiePolicyText()
    {
        $value = '';
        switch ($this->configProvider->getCookiePrivacyBar()) {
            case CookiePolicyBar::NOTIFICATION:
                $value = $this->configProvider->getNotifyBarText();
                break;
            case CookiePolicyBar::CONFIRMATION:
                $value = $this->configProvider->getConfirmationBarText();
                break;
        }

        if ($value) {
            $value = $this->cmsTemplateFilter->filter($value);
        }

        return \Zend_Json::encode($value);
    }

    /**
     * @return string
     */
    public function getAllowLink()
    {
        return $this->_urlBuilder->getUrl('gdpr/cookie/allow');
    }

    /**
     * @return string
     */
    public function getDisallowLink()
    {
        return $this->_urlBuilder->getUrl('gdpr/cookie/disallow');
    }

    /**
     * @return int
     */
    public function getNoticeType()
    {
        return (int)$this->configProvider->getCookiePrivacyBar();
    }

    /**
     * @return null|string
     */
    public function getTextBarCollor()
    {
        if (!$this->hasData('text_color_cookies')) {
            $this->setData('text_color_cookies', $this->configProvider->getTextBarCollor());
        }

        return $this->getData('text_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getBackgroundBarCollor()
    {
        if(!$this->hasData('background_color_cookies')) {
            $this->setData('background_color_cookies', $this->configProvider->getBackgroundBarCollor());
        }

        return $this->getData('background_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getButtonsBarCollor()
    {
        if(!$this->hasData('buttons_color_cookies')) {
            $this->setData('buttons_color_cookies', $this->configProvider->getButtonsBarCollor());
        }

        return $this->getData('buttons_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getButtonTextBarCollor()
    {
        if(!$this->hasData('buttons_text_color_cookies')) {
            $this->setData('buttons_text_color_cookies', $this->configProvider->getButtonTextBarCollor());
        }

        return $this->getData('buttons_text_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getLinksBarCollor()
    {
        if(!$this->hasData('link_color_cookies')) {
            $this->setData('link_color_cookies', $this->configProvider->getLinksBarCollor());
        }

        return $this->getData('link_color_cookies');
    }
}
