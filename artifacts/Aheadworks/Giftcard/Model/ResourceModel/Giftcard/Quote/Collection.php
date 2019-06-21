<?php

namespace Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Quote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\Giftcard\Quote', 'Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Quote');
    }
}