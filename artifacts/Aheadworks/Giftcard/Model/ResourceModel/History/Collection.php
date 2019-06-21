<?php

namespace Aheadworks\Giftcard\Model\ResourceModel\History;

/**
 * Giftcard collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\History', 'Aheadworks\Giftcard\Model\ResourceModel\History');
    }

    public function addGiftcardFilter($giftcardId)
    {
        $this->addFieldToFilter('giftcard_id', $giftcardId);
        return $this;
    }

    protected function _afterLoadData()
    {
        foreach ($this->getItems() as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoadData();
    }
}