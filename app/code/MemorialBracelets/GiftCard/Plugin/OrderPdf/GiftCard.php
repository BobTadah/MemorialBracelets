<?php

namespace MemorialBracelets\GiftCard\Plugin\OrderPdf;

use Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory;

/**
 * Class GiftCard
 * @package MemorialBracelets\GiftCard\Plugin\Pdf
 */
class GiftCard
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * GiftCard constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $subject
     * @param $result
     * @return array
     */
    public function afterGetTotalsForDisplay(\Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $subject, $result)
    {
        if ($subject instanceof \Magento\Tax\Model\Sales\Pdf\Tax) {
            $order = $subject->getOrder();
            $fontSize = $subject->getFontSize() ? $subject->getFontSize() : 7;

            $giftCardItems = $this->collectionFactory->create()
                ->addFieldToFilter('quote_id', $order->getQuoteId());

            if ($giftCardItems->count() > 0) {
                /** @var $giftCardItem \Aheadworks\Giftcard\Model\GiftCard */
                foreach ($giftCardItems as $giftCardItem) {
                    $result[] = [
                        'amount' => $order->getOrderCurrency()->formatPrecision(
                            -$giftCardItem->getGiftcardAmount(),
                            2,
                            [],
                            false,
                            false
                        ),
                        'label' => __('Gift Card Code (%1):', $giftCardItem->getCode()),
                        'font_size' => $fontSize
                    ];
                }
            }
        }

        return $result;
    }
}
