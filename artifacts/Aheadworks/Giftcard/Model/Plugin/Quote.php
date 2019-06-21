<?php
namespace Aheadworks\Giftcard\Model\Plugin;

class Quote
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_sourceQuote;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_destQuote;

    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $_giftCardManager;

    /**
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
    ) {
        $this->_giftCardManager = $giftCardManager;
    }

    public function beforeMerge()
    {
        $arguments = func_get_args();
        $this->_destQuote = $arguments[0];
        $this->_sourceQuote = $arguments[1];
        array_shift($arguments);
        return $arguments;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     */
    public function afterMerge($quote)
    {
        try {
            $this->_giftCardManager->replaceToQuote($this->_sourceQuote->getId(), $this->_destQuote->getId());
        } catch (\Exception $e) {
        }
        return $quote;
    }
}
