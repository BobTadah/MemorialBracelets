<?php
namespace Aheadworks\Giftcard\Model\Sales\Totals;

use Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Invoice as ResourceGiftCardInvoice;
use Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Creditmemo as ResourceGiftCardCreditMemo;

/**
 * Class Creditmemo
 * @package Aheadworks\Giftcard\Model\Sales\Totals
 */
class Creditmemo extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @var ResourceGiftCardInvoice\CollectionFactory
     */
    protected $giftCardInvoiceCollectionFactory;

    /**
     * @var ResourceGiftCardCreditMemo\CollectionFactory
     */
    protected $giftCardCreditMemoCollectionFactory;

    /**
     * @param ResourceGiftCardInvoice\CollectionFactory $giftCardInvoiceCollectionFactory
     * @param ResourceGiftCardCreditMemo\CollectionFactory $giftCardCreditMemoCollectionFactory
     * @param array $data
     */
    public function __construct(
        ResourceGiftCardInvoice\CollectionFactory $giftCardInvoiceCollectionFactory,
        ResourceGiftCardCreditMemo\CollectionFactory $giftCardCreditMemoCollectionFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->giftCardInvoiceCollectionFactory = $giftCardInvoiceCollectionFactory;
        $this->giftCardCreditMemoCollectionFactory = $giftCardCreditMemoCollectionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditMemo)
    {
        parent::collect($creditMemo);

        $total = $creditMemo->getGrandTotal();
        $baseTotal = $creditMemo->getBaseGrandTotal();

        $baseTotalGiftCardAmount = $totalGiftCardAmount = 0;
        $creditMemoGiftCards = array();
        if ($total > 0 && $baseTotal > 0 && !$creditMemo->getId()) {
            /** @var $invoiceGiftCardCode \Aheadworks\Giftcard\Model\Giftcard\Invoice */
            foreach ($this->getInvoicedGiftCardCodes($creditMemo) as $invoiceGiftCardCode) {
                $baseGiftCardAmount = $invoiceGiftCardCode->getBaseGiftcardAmount();
                $giftCardAmount = $invoiceGiftCardCode->getGiftcardAmount();

                /** @var $creditMemoGiftCardCode \Aheadworks\Giftcard\Model\Giftcard\Creditmemo */
                foreach ($this->getRefundedGiftCardCodes($creditMemo, $invoiceGiftCardCode->getId()) as $creditMemoGiftCardCode) {
                    $baseGiftCardAmount -= $creditMemoGiftCardCode->getBaseGiftcardAmount();
                    $giftCardAmount -= $creditMemoGiftCardCode->getGiftcardAmount();
                }
                $baseCardUsedAmount = $baseGiftCardAmount;
                if ($baseGiftCardAmount >= $baseTotal) {
                    $baseCardUsedAmount = $baseTotal;
                }
                $baseTotal -= $baseCardUsedAmount;

                $cardUsedAmount = $giftCardAmount;
                if ($giftCardAmount >= $total) {
                    $cardUsedAmount = $total;
                }
                $total -= $cardUsedAmount;
                $baseGiftCardAmount = round($baseCardUsedAmount, 4);
                $giftCardAmount = round($cardUsedAmount, 4);

                $baseTotalGiftCardAmount += $baseGiftCardAmount;
                $totalGiftCardAmount += $giftCardAmount;

                array_push(
                    $creditMemoGiftCards,
                    new \Magento\Framework\DataObject(
                        array_merge($invoiceGiftCardCode->getData(), [
                            'base_giftcard_amount' => $baseGiftCardAmount,
                            'giftcard_amount' => $giftCardAmount
                        ])
                    )
                );
            }
        }
        if ($creditMemo->getId() && $creditMemo->getAwGiftCards()) {
            foreach ($creditMemo->getAwGiftCards() as $creditMemoGiftCard) {
                $baseTotalGiftCardAmount += $creditMemoGiftCard->getBaseGiftcardAmount();
                $totalGiftCardAmount += $creditMemoGiftCard->getGiftcardAmount();
            }
        }
        $creditMemo
            ->setAwGiftCards($creditMemoGiftCards)
            ->setBaseAwGiftCardsAmount($baseTotalGiftCardAmount)
            ->setAwGiftCardsAmount($totalGiftCardAmount)
            ->setBaseGrandTotal($creditMemo->getBaseGrandTotal() - $baseTotalGiftCardAmount)
            ->setGrandTotal($creditMemo->getGrandTotal() - $totalGiftCardAmount)
        ;
        return $this;
    }

    /**
     * Retrieves gift card codes that had been invoiced in the current order
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @return mixed
     */
    protected function getInvoicedGiftCardCodes(\Magento\Sales\Model\Order\Creditmemo $creditMemo)
    {
        $collection = $this->giftCardInvoiceCollectionFactory->create()
            ->addFieldToFilter('invoice_id', ['in' => $creditMemo->getOrder()->getInvoiceCollection()->getAllIds()])
            ->addSumBaseAmountToFilter()
            ->addSumAmountToFilter()
        ;
        $collection->getSelect()->group('giftcard_id');
        return $collection;
    }

    /**
     * Retrieves gift card codes that got refunded balance in the current order
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @param int $giftCardId
     * @return mixed
     */
    protected function getRefundedGiftCardCodes(\Magento\Sales\Model\Order\Creditmemo $creditMemo, $giftCardId)
    {
        return $this->giftCardCreditMemoCollectionFactory->create()
            ->addFieldToFilter('giftcard_id', ['eq' => $giftCardId])
            ->addFieldToFilter('order_id', ['eq' => $creditMemo->getOrderId()])
            ;
    }
}
