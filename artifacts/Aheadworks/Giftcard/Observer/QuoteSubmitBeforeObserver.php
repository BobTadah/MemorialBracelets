<?php
namespace Aheadworks\Giftcard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class QuoteSubmitBeforeObserver
 * @package Aheadworks\Giftcard\Observer
 */
class QuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $baseGiftCardAmount = $quote->getDataUsingMethod('base_aw_gift_card_amount');
        if ($baseGiftCardAmount > 0) {
            $order->setBaseAwGiftCardAmount($baseGiftCardAmount);
        }
    }
}
