<?php
namespace Aheadworks\Giftcard\Model\Plugin;

use Aheadworks\Giftcard\Model\ResourceModel\Giftcard as ResourceGiftCard;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class Order
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $giftCardManager;

    /**
     * @var \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory
     */
    protected $giftCardCollectionFactory;

    /**
     * @var \Aheadworks\Giftcard\Model\Statistics
     */
    protected $statistics;

    /**
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     * @param ResourceGiftCard\CollectionFactory $giftCardCollectionFactory
     * @param \Aheadworks\Giftcard\Model\Statistics $statistics
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager,
        ResourceGiftCard\CollectionFactory $giftCardCollectionFactory,
        \Aheadworks\Giftcard\Model\Statistics $statistics
    ) {
        $this->giftCardCollectionFactory = $giftCardCollectionFactory;
        $this->statistics = $statistics;
        $this->giftCardManager = $giftCardManager;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order
     */
    public function afterPlace($order)
    {
        /** @var $giftCard \Aheadworks\Giftcard\Model\Giftcard */
        foreach ($this->getUsedGiftCardCodes($order) as $giftCardCode) {
            $giftCardCode
                ->setOrder($order)
                ->setBalance($giftCardCode->getBalance() - $giftCardCode->getBaseGiftcardAmount())
                ->save()
            ;
        }
        return $order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order
     */
    public function afterCancel($order)
    {
        /** @var $giftCardCode \Aheadworks\Giftcard\Model\Giftcard */
        foreach ($this->getUsedGiftCardCodes($order) as $giftCardCode) {
            try {
                if ($giftCardCode->getProductId()) {
                    $data = [
                        'used_amount' => -$giftCardCode->getBaseGiftcardAmount()
                    ];
                    if ($giftCardCode->isUsed()) {
                        $data['used_qty'] = -1;
                    }
                    $this->statistics->updateStatistics(
                        $giftCardCode->getProductId(),
                        $order->getStoreId(),
                        $data
                    );
                }

                $this->giftCardManager->updateBalance(
                    $giftCardCode->getId(),
                    $giftCardCode->getBaseGiftcardAmount(),
                    $order->getStoreId(),
                    true
                );
            } catch (\Exception $e) {
                // todo: log exception
            }
        }
        return $order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function beforeCanCreditmemo($order)
    {
        if (
            !$order->isCanceled()
            && $order->getState() !== \Magento\Sales\Model\Order::STATE_CLOSED
            && $this->getUsedGiftCardCodes($order)->getSize() > 0
        ) {
            foreach ($order->getAllItems() as $item) {
                if ($item->canRefund()) {
                    $order->setForcedCanCreditmemo(true);
                }
            }
        }
    }

    /**
     * Retrieves gift card codes that had been used for pay given order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Collection
     */
    protected function getUsedGiftCardCodes($order)
    {
        return $this->giftCardCollectionFactory->create()
            ->addFieldToFilter('quote_id', $order->getQuoteId())
            ;
    }
}
