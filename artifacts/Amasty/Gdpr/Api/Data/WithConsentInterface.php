<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Api\Data;

interface WithConsentInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const DATE_CONSENTED = 'date_consented';
    const POLICY_VERSION = 'policy_version';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setId($id);

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getDateConsented();

    /**
     * @param string $dateConsented
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setDateConsented($dateConsented);

    /**
     * @return string
     */
    public function getPolicyVersion();

    /**
     * @param string $policyVersion
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setPolicyVersion($policyVersion);
}
