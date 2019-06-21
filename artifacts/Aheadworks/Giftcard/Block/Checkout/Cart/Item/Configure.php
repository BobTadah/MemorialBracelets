<?php
namespace Aheadworks\Giftcard\Block\Checkout\Cart\Item;

class Configure extends \Magento\Checkout\Block\Cart\Item\Configure
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Configure product view blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $quote = $this->_checkoutSession->getQuote();
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $block->setQuoteItem($quote->getItemById($this->getRequest()->getParam('id')));
            $optionsBlock = $block->getChildBlock('giftcard_options');
            if ($optionsBlock) {
                $optionsBlock->setQuoteItem($quote->getItemById($this->getRequest()->getParam('id')));
            }
        }
        return parent::_prepareLayout();
    }
}
