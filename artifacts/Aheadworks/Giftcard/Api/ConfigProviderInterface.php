<?php
namespace Aheadworks\Giftcard\Api;

/**
 * Interface ConfigProviderInterface
 * @package Aheadworks\Giftcard\Api
 * @api
 */
interface ConfigProviderInterface
{
    /**
     * @return \Aheadworks\Giftcard\Api\Data\GiftcardInterface[]
     */
    public function getAppliedGiftCardsData();
}
