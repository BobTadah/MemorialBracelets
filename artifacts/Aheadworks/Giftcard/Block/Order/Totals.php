<?php
namespace Aheadworks\Giftcard\Block\Order;

use Aheadworks\Giftcard\Model\ResourceModel\Giftcard as ResourceGiftCard;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ResourceGiftCard\CollectionFactory
     */
    protected $_giftCardCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param ResourceGiftCard\CollectionFactory $giftCardCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ResourceGiftCard\CollectionFactory $giftCardCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_giftCardCollectionFactory = $giftCardCollectionFactory;
    }

    /**
     * Get totals source object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        // todo: load from according source table: aw_giftcard_quote, aw_giftcard_invoice, aw_giftcard_creditmemo

        $giftCardItemsCollection = $this->_giftCardCollectionFactory->create()
            ->addFieldToFilter('quote_id', $this->_getQuoteId())
            ->getItems()
        ;
        /** @var $giftCardItem \Aheadworks\Giftcard\Model\Giftcard */
        foreach ($giftCardItemsCollection as $giftCardItem) {
            $this->getParentBlock()->addTotal(new \Magento\Framework\DataObject(
                [
                    'code' => $this->getNameInLayout() . $giftCardItem->getId(),
                    'label' => __('Gift Card Code (%1)', $giftCardItem->getCode()),
                    'value' => -$giftCardItem->getGiftcardAmount(),
                ]
            ));
        }
        return $this;
    }

    /**
     * Get quote id
     *
     * @return int
     */
    protected function _getQuoteId()
    {
        $quote_id = 0;
        $source = $this->getSource();
        if ($source instanceof \Magento\Sales\Model\Order) {
            $quote_id = $source->getQuoteId();
        }
        if (
            $source instanceof \Magento\Sales\Model\Order\Invoice ||
            $source instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            $quote_id = $source->getOrder()->getQuoteId();
        }
        return $quote_id;
    }
}
