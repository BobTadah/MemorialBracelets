<?php
namespace Aheadworks\Giftcard\Model\ResourceModel\Statistics;

/**
 * Statistics collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\Statistics', 'Aheadworks\Giftcard\Model\ResourceModel\Statistics');
    }
}