<?php
namespace Aheadworks\Giftcard\Model\Plugin\Order;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Invoice
 * @package Aheadworks\Giftcard\Model\Plugin\Order
 */
class Invoice
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $giftCardManager;

    /**
     * @var \Aheadworks\Giftcard\Model\Email\Sender
     */
    protected $sender;

    /**
     * @var \Aheadworks\Giftcard\Model\Statistics
     */
    protected $statistics;

    /**
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     * @param \Aheadworks\Giftcard\Model\Email\Sender $sender
     * @param \Aheadworks\Giftcard\Model\Statistics $statistics
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager,
        \Aheadworks\Giftcard\Model\Email\Sender $sender,
        \Aheadworks\Giftcard\Model\Statistics $statistics
    ) {
        $this->giftCardManager = $giftCardManager;
        $this->sender = $sender;
        $this->statistics = $statistics;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function afterSave($invoice)
    {
        if ($invoice->getAwGiftCards()) {
            foreach ($invoice->getAwGiftCards() as $giftCardInvoice) {
                try {
                    $this->giftCardManager->addToInvoice($invoice, $giftCardInvoice);
                    if ($giftCardInvoice->getProductId()) {
                        $this->statistics->updateStatistics(
                            $giftCardInvoice->getProductId(),
                            $invoice->getStoreId(),
                            $this->_getUsedStatData($giftCardInvoice)
                        );
                    }
                } catch (\Exception $e) {
                    // todo: log exception
                }
            }
        }

        if ($invoice->wasPayCalled()) {
            foreach ($invoice->getAllItems() as $item) {
                /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
                if ($item->getOrderItem()->getProductType() == TypeGiftCard::TYPE_CODE) {
                    $options = $item->getOrderItem()->getProductOptions();
                    $giftCardProductOptions = $this->_getGiftCardProductOptions($item, $invoice->getStoreId());
                    $qty = $this->_getQtyToCreate($item);
                    $expiredAt = null;
                    $createdCodes = [];
                    while ($qty-- > 0) {
                        try {
                            $giftCardCode = $this->giftCardManager
                                ->create(
                                    $giftCardProductOptions[TypeGiftCard::ATTRIBUTE_CODE_TYPE],
                                    $item->getBasePrice(),
                                    $giftCardProductOptions[TypeGiftCard::ATTRIBUTE_CODE_EXPIRE],
                                    $invoice->getStore()->getWebsiteId(),
                                    $this->_getSenderData($options),
                                    $this->_getRecipientData($options),
                                    $options[TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE],
                                    $item->getOrderItem()->getProductId(),
                                    $invoice->getOrder()
                                );
                            $createdCodes[] = $giftCardCode->getCode();
                            $expiredAt = $giftCardCode->getExpireAt() ? $giftCardCode->getExpireAt() : null;
                        } catch (\Exception $e) {
                            // todo: log exception
                        }
                    }
                    $options['aw_gc_created_codes'] = isset($options['aw_gc_created_codes']) ?
                        array_merge($options['aw_gc_created_codes'], $createdCodes) :
                        $createdCodes;
                    $this->statistics->updateStatistics(
                        $item->getOrderItem()->getProductId(),
                        $invoice->getStoreId(),
                        $this->_getPurchasedStatData($item)
                    );
                    try {
                        $this->sender->prepareAndSend(
                            array_merge(
                                $options,
                                $giftCardProductOptions,
                                [
                                    'store' => $invoice->getStore(),
                                    'store_id' => $invoice->getStoreId(),
                                    'giftcard_codes' => $createdCodes,
                                    'balance' => $item->getBasePrice(),
                                    'currency_code' => $invoice->getOrderCurrencyCode(),
                                    'expired_at' => $expiredAt
                                ]
                            )
                        );
                        $options['email_sent'] = true;
                    } catch (\Exception $e) {
                        $options['email_sent'] = false;
                        // todo: log exception
                    }
                    $item->getOrderItem()
                        ->setProductOptions($options)
                        ->save()
                    ;
                }
            }
        }
        return $invoice;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice\Item $item
     * @param int $storeId
     * @return mixed
     */
    protected function _getGiftCardProductOptions(\Magento\Sales\Model\Order\Invoice\Item $item, $storeId)
    {
        return $item->getOrderItem()->getProduct()->getTypeInstance()->getGiftCardProductOptions(
            $item->getOrderItem()->getProduct(),
            $storeId
        );
    }

    /**
     * Retrieves qty of codes to create
     *
     * @param \Magento\Sales\Model\Order\Invoice\Item $item
     * @return int
     */
    protected function _getQtyToCreate(\Magento\Sales\Model\Order\Invoice\Item $item)
    {
        $orderItem = $item->getOrderItem();
        $qty = $orderItem->getQtyInvoiced();
        $createdCodes = $orderItem->getProductOptionByCode('aw_gc_created_codes');
        if ($createdCodes && count($createdCodes) > 0) {
            $qty -= count($createdCodes);
        }
        return $qty;
    }

    /**
     * @param array $productOptions
     * @return array
     */
    protected function _getSenderData($productOptions)
    {
        $name = $productOptions[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME];
        $email = isset($productOptions[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL]) ?
            $productOptions[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL] :
            ''
        ;
        return [
            'senderName' => $name,
            'senderEmail' => $email
        ];
    }

    /**
     * @param array $productOptions
     * @return array
     */
    protected function _getRecipientData($productOptions)
    {
        $name = $productOptions[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME];
        $email = isset($productOptions[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL]) ?
            $productOptions[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL] :
            ''
        ;
        return [
            'recipientName' => $name,
            'recipientEmail' => $email
        ];
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice\Item $item
     * @return array
     */
    protected function _getPurchasedStatData(\Magento\Sales\Model\Order\Invoice\Item $item)
    {
        return [
            'purchased_qty' => $item->getOrderItem()->getQtyInvoiced(),
            'purchased_amount' => $item->getOrderItem()->getBaseRowInvoiced()
        ];
    }

    /**
     * @param \Magento\Framework\DataObject $giftCardInvoice
     * @return array
     */
    protected function _getUsedStatData(\Magento\Framework\DataObject $giftCardInvoice)
    {
        return [
            'used_qty' => $giftCardInvoice->getBalance() > 0 ? 0 : 1,
            'used_amount' => $giftCardInvoice->getBaseGiftcardAmount()
        ];
    }
}
