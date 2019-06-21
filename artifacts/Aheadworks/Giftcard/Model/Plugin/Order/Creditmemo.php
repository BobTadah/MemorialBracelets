<?php
namespace Aheadworks\Giftcard\Model\Plugin\Order;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Creditmemo
 * @package Aheadworks\Giftcard\Model\Plugin\Order
 */
class Creditmemo
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     */
    public function beforeIsValidGrandTotal($creditmemo)
    {
        if ($creditmemo->getAwGiftCards()) {
            $creditmemo->setAllowZeroGrandTotal(true);
        }
    }
}
