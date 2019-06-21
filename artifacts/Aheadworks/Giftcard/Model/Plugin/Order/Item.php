<?php
namespace Aheadworks\Giftcard\Model\Plugin\Order;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Item
 * @package Aheadworks\Giftcard\Model\Plugin\Order
 */
class Item
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $giftCardManager;

    /**
     * @var \Aheadworks\Giftcard\Model\Statistics
     */
    protected $statistics;

    /**
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     * @param \Aheadworks\Giftcard\Model\Statistics $statistics
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager,
        \Aheadworks\Giftcard\Model\Statistics $statistics
    ) {
        $this->giftCardManager = $giftCardManager;
        $this->statistics = $statistics;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Sales\Model\Order\Item
     */
    public function afterCancel($item)
    {
        if ($item->getProductType() == TypeGiftCard::TYPE_CODE) {
            try {
                $qty = $item->getQtyToCancel();
                $this->giftCardManager->refund(
                    $item->getProductOptionByCode('aw_gc_created_codes'),
                    $qty,
                    $item->getBasePrice()
                );
                $this->statistics->updateStatistics(
                    $item->getProductId(),
                    $item->getStoreId(),
                    [
                        'purchased_qty' => -$qty,
                        'purchased_amount' => -$qty * $item->getBasePrice()
                    ]
                );
            } catch (\Exception $e) {
                // todo: log exception
            }
        }
        return $item;
    }
}
