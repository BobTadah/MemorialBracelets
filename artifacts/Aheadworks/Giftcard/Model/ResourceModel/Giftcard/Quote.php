<?php
namespace Aheadworks\Giftcard\Model\ResourceModel\Giftcard;

use Magento\Framework\Exception\LocalizedException;

class Quote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('aw_giftcard_quote', 'id');
    }

    public function exists($giftCardId, $quoteId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select();
        $select
            ->from($this->getMainTable(), ['id'])
            ->where('giftcard_id = ?', $giftCardId)
            ->where('quote_id = ?', $quoteId)
        ;
        $result = $adapter->fetchOne($select);
        return !empty($result);
    }

    public function remove($giftCardId, $quoteId)
    {
        $adapter = $this->getConnection();
        $adapter->delete($this->getMainTable(), [
            'giftcard_id=?' => $giftCardId,
            'quote_id=?' => $quoteId
        ]);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $giftCardQuote)
    {
        if (!$giftCardQuote->getQuoteId()) {
            throw new LocalizedException(__('Unable to apply Gift Card Code'));
        }
        return $this;
    }
}