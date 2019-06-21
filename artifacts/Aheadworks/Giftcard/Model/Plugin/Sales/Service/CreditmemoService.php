<?php
namespace Aheadworks\Giftcard\Model\Plugin\Sales\Service;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class CreditmemoService
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $giftCardManager;

    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardFactory
     */
    protected $giftCardCodeFactory;

    /**
     * @var \Aheadworks\Giftcard\Model\Statistics
     */
    protected $statistics;

    /**
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     * @param \Aheadworks\Giftcard\Model\GiftcardFactory $giftCardCodeFactory
     * @param \Aheadworks\Giftcard\Model\Statistics $statistics
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager,
        \Aheadworks\Giftcard\Model\GiftcardFactory $giftCardCodeFactory,
        \Aheadworks\Giftcard\Model\Statistics $statistics
    ) {
        $this->giftCardManager = $giftCardManager;
        $this->giftCardCodeFactory = $giftCardCodeFactory;
        $this->statistics = $statistics;
    }

    /**
     * @param \Magento\Sales\Model\Service\CreditmemoService $creditMemoService
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function afterRefund($creditMemoService, $creditMemo)
    {
        if ($creditMemo->getAwGiftCards()) {
            /** @var $giftCardCreditMemo \Aheadworks\Giftcard\Model\Giftcard\Creditmemo */
            foreach ($creditMemo->getAwGiftCards() as $giftCardCreditMemo) {
                try {
                    /** @var \Aheadworks\Giftcard\Model\Giftcard $giftCardCode */
                    $giftCardCode = $this->giftCardCodeFactory->create()
                        ->load($giftCardCreditMemo->getGiftcardId())
                    ;
                    if ($giftCardCode->getProductId()) {
                        $data = [
                            'used_amount' => -$giftCardCreditMemo->getBaseGiftcardAmount()
                        ];
                        if ($giftCardCode->isUsed()) {
                            $data['used_qty'] = -1;
                        }
                        $this->statistics->updateStatistics(
                            $giftCardCode->getProductId(),
                            $creditMemo->getStoreId(),
                            $data
                        );
                    }

                    $this->giftCardManager->updateBalance(
                        $giftCardCreditMemo->getGiftcardId(),
                        $giftCardCreditMemo->getBaseGiftcardAmount(),
                        $creditMemo->getStoreId(),
                        true
                    );
                } catch (\Exception $e) {
                    // todo: log exception
                }
            }
        }
        foreach ($creditMemo->getAllItems() as $item) {
            if ($item->getOrderItem()->getProductType() == TypeGiftCard::TYPE_CODE) {
                try {
                    $qty = $item->getQty();
                    $this->giftCardManager->refund(
                        $item->getOrderItem()->getProductOptionByCode('aw_gc_created_codes'),
                        $qty,
                        $item->getBasePrice(),
                        $creditMemo
                    );
                    $this->statistics->updateStatistics(
                        $item->getOrderItem()->getProductId(),
                        $creditMemo->getStoreId(),
                        [
                            'purchased_qty' => -$qty,
                            'purchased_amount' => -$qty * $item->getBasePrice()
                        ]
                    );

                } catch (\Exception $e) {
                    // todo: log exception
                }
            }
        }
        return $creditMemo;
    }
}
