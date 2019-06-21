<?php
namespace Aheadworks\Giftcard\Model\Data;

use Aheadworks\Giftcard\Api\Data\GiftcardInterface;

/**
 * Class Giftcard
 * @package Aheadworks\Giftcard\Model\Data
 */
class Giftcard extends \Magento\Framework\Api\AbstractSimpleObject implements GiftcardInterface
{
    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_get(GiftcardInterface::CODE);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->_get(GiftcardInterface::AMOUNT);
    }

    /**
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->_get(GiftcardInterface::REMOVE_URL);
    }
}
