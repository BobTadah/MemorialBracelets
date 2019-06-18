<?php
namespace MemorialBracelets\PerfectAudienceTag\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class PerfectAudienceTag extends Template
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * PerfectAudienceTag constructor.
     * @param Template\Context $context
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CheckoutSession $checkoutSession,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->priceCurrency = $priceCurrency;
    }

    /** Returns Perfect Audience Tag for Order Confirmation Page. */
    public function getPerfectAudienceTag()
    {
        /** @var  $order, get order from checkout session to grab id and details. */
        $order = $this->checkoutSession->getLastRealOrder();
        $orderID = $order->getIncrementId();
        $total = $this->priceCurrency->round($this->pricingHelper->currency($order->getBaseGrandTotal(), false));

        return '<script type="text/javascript">
            (function() {
            window._pa = window._pa || {};
             _pa.orderId = ' . $orderID . ';
             _pa.revenue = ' . $total . ';
            var pa = document.createElement(\'script\'); pa.type = \'text/javascript\'; pa.async = true;
            pa.src = (\'https:\' == document.location.protocol ? \'https:\' : \'http:\') + "//tag.marinsm.com/serve/50664c808c54f60002000006.js";
            var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(pa, s);
            })();
            </script>';
    }
}
