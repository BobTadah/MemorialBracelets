<?php
namespace Aheadworks\Giftcard\Model\Sales\Totals;

use Aheadworks\Giftcard\Model\ResourceModel\Giftcard as ResourceGiftCard;
use Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Invoice as ResourceGiftCardInvoice;

/**
 * Class Subtotal
 * @package Aheadworks\Giftcard\Model\Sales\Totals
 */
class Invoice extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @var ResourceGiftCard\CollectionFactory
     */
    protected $giftCardCollectionFactory;

    /**
     * @var ResourceGiftCardInvoice\CollectionFactory
     */
    protected $giftCardInvoiceCollectionFactory;

    /**
     * @param ResourceGiftCard\CollectionFactory $giftCardCollectionFactory
     * @param ResourceGiftCardInvoice\CollectionFactory $giftCardInvoiceCollectionFactory
     * @param array $data
     */
    public function __construct(
        ResourceGiftCard\CollectionFactory $giftCardCollectionFactory,
        ResourceGiftCardInvoice\CollectionFactory $giftCardInvoiceCollectionFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->giftCardCollectionFactory = $giftCardCollectionFactory;
        $this->giftCardInvoiceCollectionFactory = $giftCardInvoiceCollectionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this|void
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);

        $baseTotal = $invoice->getBaseGrandTotal();
        $total = $invoice->getGrandTotal();

        $baseTotalGiftCardAmount = $totalGiftCardAmount = 0;
        $invoiceGiftCards = array();
        if (!$invoice->getId()) {
            /** @var $giftCard \Aheadworks\Giftcard\Model\Giftcard */
            foreach ($this->getUsedGiftCardCodes($invoice) as $giftCard) {
                $baseGiftCardAmount = $giftCard->getBaseGiftcardAmount();
                $giftCardAmount = $giftCard->getGiftcardAmount();

                /** @var $giftCardInvoice \Aheadworks\Giftcard\Model\Giftcard\Invoice */
                foreach ($this->getInvoicedGiftCardCodes($invoice, $giftCard->getId()) as $giftCardInvoice) {
                    $baseGiftCardAmount -= $giftCardInvoice->getBaseGiftcardAmount();
                    $giftCardAmount -= $giftCardInvoice->getGiftcardAmount();
                }
                $baseGiftCardUsedAmount = $baseGiftCardAmount;
                if ($baseGiftCardAmount >= $baseTotal) {
                    $baseGiftCardUsedAmount = $baseTotal;
                }
                $baseTotal -= $baseGiftCardUsedAmount;

                $giftCardUsedAmount = $giftCardAmount;
                if ($giftCardAmount >= $total) {
                    $giftCardUsedAmount = $total;
                }
                $total -= $giftCardUsedAmount;

                $baseGiftCardAmount = round($baseGiftCardUsedAmount, 4);
                $giftCardAmount = round($giftCardUsedAmount, 4);

                $baseTotalGiftCardAmount += $baseGiftCardAmount;
                $totalGiftCardAmount += $giftCardAmount;

                array_push(
                    $invoiceGiftCards,
                    new \Magento\Framework\DataObject(
                        array_merge($giftCard->getData(), [
                            'base_giftcard_amount' => $baseGiftCardAmount,
                            'giftcard_amount' => $giftCardAmount
                        ])
                    )
                );
            }
        }
        if ($invoice->getId() && $invoice->getAwGiftCards()) {
            foreach ($invoice->getAwGiftCards() as $invoiceGiftCard) {
                $baseTotalGiftCardAmount += $invoiceGiftCard->getBaseGiftcardAmount();
                $totalGiftCardAmount += $invoiceGiftCard->getGiftcardAmount();
            }
        }
        $invoice
            ->setAwGiftCards($invoiceGiftCards)
            ->setBaseAwGiftCardsAmount($baseTotalGiftCardAmount)
            ->setAwGiftCardsAmount($totalGiftCardAmount)
            ->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalGiftCardAmount)
            ->setGrandTotal($invoice->getGrandTotal() - $totalGiftCardAmount)
        ;
        return $this;
    }

    /**
     * Retrieves gift card codes with the given ID that had been invoiced in the given order
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param $giftCardId
     */
    protected function getInvoicedGiftCardCodes(\Magento\Sales\Model\Order\Invoice $invoice, $giftCardId)
    {
        return $this->giftCardInvoiceCollectionFactory->create()
            ->addFieldToFilter('giftcard_id', ['eq' => $giftCardId])
            ->addFieldToFilter('order_id', ['eq' => $invoice->getOrderId()])
            ;
    }

    /**
     * Retrieves gift card codes that had been used for pay given order
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return mixed
     */
    protected function getUsedGiftCardCodes(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        return $this->giftCardCollectionFactory->create()
            ->addFieldToFilter('quote_id', $invoice->getOrder()->getQuoteId())
            ;
    }
}
