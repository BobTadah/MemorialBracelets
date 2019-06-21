<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent as WithConsentResource;
use Magento\Framework\Model\AbstractModel;

class WithConsent extends AbstractModel implements WithConsentInterface
{
    public function _construct()
    {
        parent::_construct();

        $this->_init(WithConsentResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(WithConsentInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(WithConsentInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateConsented()
    {
        return $this->_getData(WithConsentInterface::DATE_CONSENTED);
    }

    /**
     * @inheritdoc
     */
    public function setDateConsented($dateConsented)
    {
        $this->setData(WithConsentInterface::DATE_CONSENTED, $dateConsented);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPolicyVersion()
    {
        return $this->_getData(WithConsentInterface::POLICY_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function setPolicyVersion($policyVersion)
    {
        $this->setData(WithConsentInterface::POLICY_VERSION, $policyVersion);

        return $this;
    }
}
