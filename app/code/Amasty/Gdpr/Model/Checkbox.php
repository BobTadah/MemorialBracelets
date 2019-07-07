<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;

class Checkbox
{
    const AREA_REGISTRATION = 'registration';
    const AREA_CHECKOUT = 'checkout';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    public function __construct(
        Config $config,
        Visitor $visitor,
        PolicyRepositoryInterface $policyRepository
    ) {
        $this->config = $config;
        $this->visitor = $visitor;
        $this->policyRepository = $policyRepository;
    }

    /**
     * @return string
     */
    public function getConsentText()
    {
        return $this->config->getValue('privacy_checkbox/consent_text');
    }

    /**
     * @param $area
     *
     * @return bool
     */
    public function isEnabled($area)
    {
        if (!$this->config->isSetFlag('privacy_checkbox/display_at_' . $area)) {
            return false;
        }

        if (!$this->policyRepository->getCurrentPolicy()) {
            return false;
        }

        return true;
    }

    /**
     * Is visible for current visitor
     *
     * @param $area
     *
     * @return bool
     */
    public function isVisible($area)
    {
        if (!$this->isEnabled($area)) {
            return false;
        }

        if ($this->config->isSetFlag('privacy_checkbox/eea_only')) {
            if (!$this->visitor->isEEACustomer()) {
                return false;
            }
        }

        return true;
    }
}
