<?php
namespace Aheadworks\Giftcard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class UpdateExcludedFieldListObserver implements ObserverInterface
{
    /**
     * Exclude Giftcard attributes from Update Attributes mass-action form
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getObject();
        $list = $block->getFormExcludedFieldList();
        $excludedAttributes = [
            TypeGiftCard::ATTRIBUTE_CODE_EMAIL_TEMPLATES,
            TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS,
        ];
        $list = array_merge($list, $excludedAttributes);
        $block->setFormExcludedFieldList($list);
        return $this;
    }
}
