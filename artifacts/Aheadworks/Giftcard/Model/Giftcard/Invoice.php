<?php
namespace Aheadworks\Giftcard\Model\Giftcard;

/**
 * Gift Card Invoice Model
 *
 * @method float getGiftcardAmount() getGiftcardAmount()
 * @method float getBaseGiftcardAmount() getBaseGiftcardAmount()
 */
class Invoice extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Invoice');
    }
}