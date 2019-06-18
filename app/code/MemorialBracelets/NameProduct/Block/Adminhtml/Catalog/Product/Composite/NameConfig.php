<?php

namespace MemorialBracelets\NameProduct\Block\Adminhtml\Catalog\Product\Composite;

use Magento\Backend\Block\Template;
use IWD\OrderManager\Model\Order\Item as OrderItem;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class NameConfig extends Template
{
    /** @var OrderItem  */
    protected $orderItem;

    /** @var QuoteItem  */
    protected $quoteItem;

    /**
     * @param Template\Context $context
     * @param OrderItem $orderItem
     * @param array $data
     */
    public function __construct(Template\Context $context, OrderItem $orderItem, QuoteItem $quoteItem, array $data = [])
    {
        parent::__construct($context, $data);
        $this->orderItem = $orderItem;
        $this->quoteItem = $quoteItem;
    }

    /**
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem->load($this->getOrderItemId());
    }
}
