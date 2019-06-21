<?php
namespace Aheadworks\Giftcard\Model\Sales\Totals;

use Aheadworks\Giftcard\Model\Giftcard;
use Aheadworks\Giftcard\Model\ResourceModel\Giftcard as ResourceGiftCard;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Quote
 * @package Aheadworks\Giftcard\Model\Sales\Totals
 */
class Quote extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory
     */
    protected $giftCardCollectionFactory;

    /**
     * @var \Aheadworks\Giftcard\Model\Giftcard\QuoteFactory
     */
    protected $giftCardQuoteFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param ResourceGiftCard\CollectionFactory $giftCardCollectionFactory
     * @param Giftcard\QuoteFactory $giftCardQuoteFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ResourceGiftCard\CollectionFactory $giftCardCollectionFactory,
        Giftcard\QuoteFactory $giftCardQuoteFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->giftCardCollectionFactory = $giftCardCollectionFactory;
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Collect totals process
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $baseGrandTotal = $total->getBaseGrandTotal();
        $grandTotal = $total->getGrandTotal();

        $baseTotalGiftCardAmount = $totalGiftCardAmount = 0;
        if ($baseGrandTotal && $quote->getIsActive()) {
            $giftCardCollection = $this->giftCardCollectionFactory->create()
                ->addFieldToFilter('quote_id', $quote->getId())
                ->addOrder('balance', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ;
            /** @var $giftCard \Aheadworks\Giftcard\Model\Giftcard */
            foreach ($giftCardCollection as $giftCard) {
                $baseGiftCardAmount = $giftCard->getBalance();
                $baseGiftCardUsedAmount = $baseGiftCardAmount;
                if ($baseGiftCardUsedAmount >= $baseGrandTotal) {
                    $baseGiftCardUsedAmount = $baseGrandTotal;
                }
                $baseGrandTotal -= $baseGiftCardUsedAmount;

                $giftCardAmount = $this->priceCurrency->convert($baseGiftCardAmount);
                $giftCardUsedAmount = $giftCardAmount;
                if ($giftCardUsedAmount >= $grandTotal) {
                    $giftCardUsedAmount = $grandTotal;
                }
                $grandTotal -= $giftCardUsedAmount;

                $baseGiftCardAmount = round($baseGiftCardUsedAmount, 4);
                $giftCardAmount = round($giftCardUsedAmount, 4);

                $baseTotalGiftCardAmount += $baseGiftCardAmount;
                $totalGiftCardAmount += $giftCardAmount;

                /** @var $giftCardQuote \Aheadworks\Giftcard\Model\Giftcard\Quote */
                $giftCardQuote = $this->giftCardQuoteFactory->create()->load($giftCard->getReferenceId());
                if ($giftCardQuote->getId()) {
                    $giftCardQuote
                        ->addData([
                            'base_giftcard_amount' => $baseGiftCardAmount,
                            'giftcard_amount' => $giftCardAmount
                        ])
                        ->save()
                    ;
                }
            }
            $quote
                ->setBaseAwGiftCardAmount($baseTotalGiftCardAmount)
                ->setAwGiftCardAmount($totalGiftCardAmount)
            ;
            $total
                ->setBaseAwGiftCardAmount($baseTotalGiftCardAmount)
                ->setAwGiftCardAmount($totalGiftCardAmount)
                ->setBaseGrandTotal($total->getBaseGrandTotal() - $baseTotalGiftCardAmount)
                ->setGrandTotal($total->getGrandTotal() - $totalGiftCardAmount)
            ;
        }
        return $this;
    }

    /**
     * Fetch
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return [
            'code' => $this->getCode(),
            'title' => __('Gift Card'),
            'value' => -$total->getAwGiftCardAmount(),
            'gift_cards' => $this->giftCardCollectionFactory->create()
                    ->addFieldToFilter('quote_id', $quote->getId())
        ];
    }
}
