<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Setup\Operation;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Policy extends AbstractDb
{
    public function _construct()
    {
        $this->_init(Operation\CreatePolicyTable::TABLE_NAME, 'id');
    }

    /**
     * @param $except
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function disableAllPolicies($except)
    {
        $this->getConnection()->update(
            $this->getMainTable(),
            ['status' => \Amasty\Gdpr\Model\Policy::STATUS_DISABLED],
            ['id != ?' => $except]
        );
    }
}
