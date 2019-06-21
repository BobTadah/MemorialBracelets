<?php

namespace Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Invoice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\Giftcard\Invoice', 'Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Invoice');
    }

    public function addSumBaseAmountToFilter()
    {
        $this->addExpressionFieldToSelect(
            'base_giftcard_amount',
            'SUM({{base_giftcard_amount}})',
            'base_giftcard_amount'
        );
        return $this;
    }

    public function addSumAmountToFilter()
    {
        $this->addExpressionFieldToSelect(
            'giftcard_amount',
            'SUM({{giftcard_amount}})',
            'giftcard_amount'
        );
        return $this;
    }
}