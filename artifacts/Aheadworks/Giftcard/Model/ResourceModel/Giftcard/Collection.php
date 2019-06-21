<?php

namespace Aheadworks\Giftcard\Model\ResourceModel\Giftcard;

/**
 * Giftcard collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\Giftcard', 'Aheadworks\Giftcard\Model\ResourceModel\Giftcard');
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'quote_id') {
            return $this->addQuoteFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    public function addQuoteFilter($quoteId)
    {
        $this->joinGiftCardQuoteTable();
        $this->getSelect()
            ->where('giftcard_quote_table.quote_id = ?', $quoteId)
        ;
        return $this;
    }

    public function addNotInQuoteFilter($quoteId)
    {
        $adapter = $this->getConnection();
        $select = clone $this->getSelect();
        $select
            ->reset()
            ->from($this->getTable('aw_giftcard_quote'), 'giftcard_id')
            ->where('quote_id = ?', $quoteId)
        ;
        $ids = [];
        foreach ($adapter->fetchAll($select) as $row) {
            $ids[] = $row['giftcard_id'];
        }
        if (!empty($ids)) {
            $this->addFieldToFilter('id', ['nin' => $ids]);
        }
        return $this;
    }

    protected function joinGiftCardQuoteTable()
    {
        $this->getSelect()->join(
            ['giftcard_quote_table' => $this->getTable('aw_giftcard_quote')],
            'main_table.id = giftcard_quote_table.giftcard_id',
            [
                'reference_id' => 'giftcard_quote_table.id',
                'giftcard_amount' => 'giftcard_quote_table.giftcard_amount',
                'base_giftcard_amount' => 'giftcard_quote_table.base_giftcard_amount'
            ]
        );
    }
}