<?php
namespace Aheadworks\Giftcard\Pricing\Render;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;

/**
 * Class for final_price rendering
 *
 * @method bool getUseLinkForAsLowAs()
 * @method bool getDisplayMinimalPrice()
 */
class FinalPriceBox extends BasePriceBox
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->wrapResult(parent::_toHtml());
    }

    /**
     * Wrap with standard required container
     *
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '"' .
            '>' . $html . '</div>';
    }

    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinimalPrice()
    {
        return $this->getPrice()->getMinimalPrice();
    }

    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaximalPrice()
    {
        return $this->getPrice()->getMaximalPrice();
    }

    public function getAmount()
    {
        return $this->getMinimalPrice() ? $this->getMinimalPrice() : $this->getMaximalPrice();
    }

    /**
     * @return bool
     */
    public function renderFromTo()
    {
        return (
            $this->getMinimalPrice() &&
            $this->getMaximalPrice() &&
            $this->getMinimalPrice()->getValue() != $this->getMaximalPrice()->getValue()
        );
    }

    public function renderSingle()
    {
        return $this->getAmount();
    }
}
