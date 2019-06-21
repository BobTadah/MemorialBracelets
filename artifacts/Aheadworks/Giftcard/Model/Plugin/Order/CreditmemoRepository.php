<?php
namespace Aheadworks\Giftcard\Model\Plugin\Order;

class CreditmemoRepository
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $giftCardManager;

    /**
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
    ) {
        $this->giftCardManager = $giftCardManager;
    }

    /**
     * @param \Magento\Sales\Model\Order\CreditmemoRepository $creditMemoRepository
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function afterSave($creditMemoRepository, $creditMemo)
    {
        if ($creditMemo && $creditMemo->getAwGiftCards()) {
            foreach ($creditMemo->getAwGiftCards() as $giftCardCreditMemo) {
                try {
                    $this->giftCardManager->addToCreditmemo($creditMemo, $giftCardCreditMemo);
                } catch (\Exception $e) {
                    // todo: log exception
                }
            }
        }
        return $creditMemo;
    }
}
