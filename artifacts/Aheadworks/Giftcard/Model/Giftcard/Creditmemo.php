<?php
namespace Aheadworks\Giftcard\Model\Giftcard;

/**
 * Gift Card Creditmemo Model
 *
 * @method float getGiftcardId() getGiftcardId()
 * @method float getGiftcardAmount() getGiftcardAmount()
 * @method float getBaseGiftcardAmount() getBaseGiftcardAmount()
 */
class Creditmemo extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Creditmemo');
    }
}