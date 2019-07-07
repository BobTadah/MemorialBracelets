<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Geoip\Model\Geolocation;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Visitor
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    public function __construct(
        Config $config,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Geolocation $geolocation,
        RemoteAddress $remoteAddress
    ) {
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->geolocation = $geolocation;
        $this->remoteAddress = $remoteAddress;
    }

    public function isEEACustomer()
    {
        $customer = $this->customerSession->getCustomer();

        if ($countryCode = $this->checkoutSession->getQuote()->getShippingAddress()->getCountry()) {
            return $this->isEEACountry($countryCode);
        }

        if ($countryCode = $this->checkoutSession->getQuote()->getBillingAddress()->getCountry()) {
            return $this->isEEACountry($countryCode);
        }

        if ($customer && ($address = $customer->getPrimaryBillingAddress())) {
            if ($countryCode = $address->getCountry()) {
                return $this->isEEACountry($countryCode);
            }
        }

        if ($countryCode = $this->locate()) {
            return $this->isEEACountry($countryCode);
        } else {
            return false;
        }
    }

    protected function locate()
    {
        if ($this->customerSession->hasData('amgdpr_country')) {
            return $this->customerSession->getData('amgdpr_country');
        }

        $geolocationResult = $this->geolocation->locate($this->getRemoteIp());

        $result = isset($geolocationResult['country']) ? $geolocationResult['country'] : false;

        $this->customerSession->setData('amgdpr_country', $result);

        return $result;
    }

    public function getRemoteIp()
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        $ip = substr($ip, 0, strrpos($ip, ".")) . '.0';

        return $ip;
    }

    protected function isEEACountry($countryCode)
    {
        return in_array($countryCode, $this->config->getEEACountryCodes());
    }
}
