<?php
namespace Aheadworks\Giftcard\Model\Giftcard;

/**
 * Quote Gift Card Model
 *
 * @method Quote setQuoteId(int) setQuoteId(int)
 */
class Quote extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Quote');
    }

    /**
     * Checks whether a record with given $giftCardId and $quoteId exists in the table
     *
     * @param int $giftCardId
     * @param int $quoteId
     * @return bool
     */
    public function exists($giftCardId, $quoteId)
    {
        return $this->getResource()->exists($giftCardId, $quoteId);
    }

    public function remove($giftCardId, $quoteId)
    {
        $this->getResource()->remove($giftCardId, $quoteId);
    }
}