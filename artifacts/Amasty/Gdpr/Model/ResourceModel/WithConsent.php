<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Setup\Operation\CreateConsentLogTable;

class WithConsent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        $this->_init(CreateConsentLogTable::TABLE_NAME, WithConsentInterface::ID);
    }
}
