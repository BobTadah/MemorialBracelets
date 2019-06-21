<?php

namespace Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Creditmemo;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\Giftcard\Creditmemo', 'Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Creditmemo');
    }
}