<?php
namespace Aheadworks\Giftcard\Api;

/**
 * Interface GiftcardManagerInterface
 * @api
 */
interface GiftcardManagerInterface
{
    /**
     * Add Gift Card to quote
     *
     * @param string|\Aheadworks\Giftcard\Model\Giftcard|int $giftCard
     * @return \Aheadworks\Giftcard\Api\Data\AddToQuoteResultInterface
     */
    public function addToQuote($giftCard);

    /**
     * Remove Gift Card from quote
     *
     * @param int|\Aheadworks\Giftcard\Model\Giftcard\Quote $giftCardQuote
     * @return bool
     */
    public function removeFromQuote($giftCardQuote);
}
