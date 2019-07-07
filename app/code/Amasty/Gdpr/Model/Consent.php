<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Api\WithConsentRepositoryInterface;

class Consent
{
    /**
     * @var WithConsentRepositoryInterface
     */
    private $withConsentRepository;

    /**
     * @var WithConsentFactory
     */
    private $consentFactory;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var ActionLogger
     */
    private $logger;

    public function __construct(
        WithConsentRepositoryInterface $withConsentRepository,
        WithConsentFactory $consentFactory,
        PolicyRepositoryInterface $policyRepository,
        ActionLogger $logger
    ) {
        $this->withConsentRepository = $withConsentRepository;
        $this->consentFactory = $consentFactory;
        $this->policyRepository = $policyRepository;
        $this->logger = $logger;
    }

    public function acceptLastVersion($customerId)
    {
        if (!$customerId) {
            return;
        }

        /** @var WithConsent $consent */
        $consent = $this->consentFactory->create();

        if ($policy = $this->policyRepository->getCurrentPolicy()) {
            $consent->setPolicyVersion($policy->getPolicyVersion());
        }

        $consent->setCustomerId($customerId);

        $this->withConsentRepository->save($consent);

        $this->logger->logAction('consent_given', $customerId);
    }
}
