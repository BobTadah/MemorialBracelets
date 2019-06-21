<?php
namespace Aheadworks\Giftcard\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Gift Card Total Row Renderer
 */
class Total extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Giftcard::checkout/total.phtml';

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_priceCurrency = $priceCurrency;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $salesConfig,
            $data
        );
    }

    public function getGiftCards()
    {
        $giftCards = $this->getTotal()->getGiftCards();
        return $giftCards ? $giftCards: [];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getTotal()->getTitle();
    }

    /**
     * @param float $amount
     * @return float
     */
    public function formatPrice($amount)
    {
        return $this->_priceCurrency->format($amount);
    }

    /**
     * @param \Aheadworks\Giftcard\Model\Giftcard $giftCard
     * @return string
     */
    public function getRemoveUrl(\Aheadworks\Giftcard\Model\Giftcard $giftCard)
    {
        return $this->getUrl(
            'awgiftcard/card/remove',
            [
                'id' => $giftCard->getReferenceId(),
                'code' => $giftCard->getCode()
            ]
        );
    }
}
