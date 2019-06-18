<?php

namespace MemorialBracelets\CartMessage\Block\Onepage;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Link extends \Magento\Checkout\Block\Onepage\Link
{
    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Data $checkoutHelper,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $checkoutHelper, $data);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get cart items and return Action button label based on product types that are in the cart
     * If there are only gift cards product types, then should return only 'Proceed to Checkout'
     * Else, return 'Enter Initials and Proceed to Checkout'
     *
     * @param void
     * @return string
     **/
    public function getProceedButtonLabel()
    {
        $quoteItems = $this->checkoutSession->getQuote()->getItems();
        foreach ($quoteItems as $item) {
            if ($item->getProductType() != Giftcard::TYPE_CODE) {
                return 'Enter Initials and Proceed to Checkout';
            }
        }
        return "Proceed to Checkout";
    }
}
