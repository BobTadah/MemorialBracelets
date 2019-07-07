<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Plugin;

use Amasty\Shiprestriction\Model\RegistryConstants;

class RestrictRates
{
    protected $_allRules = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory
     */
    protected $rateErrorFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;


    /**
     * @var \Amasty\Shiprestriction\Model\ShippingRestrictionRule
     */
    protected $shippingRestrictionRule;

    /**
     * @var \Amasty\CommonRules\Model\Config
     */
    protected $commonRulesConfig;

    /**
     * RestrictRates constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Amasty\Shiprestriction\Model\ShippingRestrictionRule $shippingRestrictionRule
     * @param \Amasty\CommonRules\Model\Config $commonRulesConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Framework\App\State $appState,
        \Amasty\Shiprestriction\Model\ShippingRestrictionRule $shippingRestrictionRule,
        \Amasty\CommonRules\Model\Config $commonRulesConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->rateErrorFactory = $rateErrorFactory;
        $this->appState = $appState;
        $this->shippingRestrictionRule = $shippingRestrictionRule;
        $this->commonRulesConfig = $commonRulesConfig;
    }

    public function aroundCollectRates(
        \Magento\Shipping\Model\Shipping $shipping,
        \Closure $closure,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $closure($request);
        $result = $shipping->getResult();

        $rates = $result->getAllRates();
        if (!count($rates)) {
            return $shipping;
        }

        $rules = $this->shippingRestrictionRule->getRestrictionRules($request);
        if (!count($rules)) {
            return $shipping;
        }

        $result->reset();

        $isEmptyResult = true;
        $lastError = __(
            'Sorry, no shipping quotes are available for the selected products and destination'
        );
        $lastRate = null;
        $isRestrict = false;

        foreach ($rates as $rate) {
            $isValid = true;
            foreach ($rules as $rule) {
                if ($rule->restrict($rate)) {
                    $lastRate = $rate;
                    $lastError = $rule->getMessage();
                    $isValid = false;
                    $isRestrict = true;
                    break;
                }
            }
            if ($isValid) {
                $result->append($rate);
                $isEmptyResult = false;
            }
        }

        $isShowMessage = $this->commonRulesConfig->getErrorMessageConfig(
            RegistryConstants::SECTION_KEY
        );
        if (!empty($lastError)
            && ($isEmptyResult
                || ($isShowMessage
                    && $isRestrict))
        ) {
            $error = $this->rateErrorFactory->create();
            $error->setCarrier($lastRate->getCarrier());
            $error->setCarrierTitle($lastRate->getCarrierTitle());
            $error->setErrorMessage($lastError);

            $result->append($error);
        }

        return $shipping;
    }
}
