<?php
namespace Aheadworks\Giftcard\Pricing\Price;

use Magento\Catalog\Model\Product;

/**
 * Class FinalPrice
 * @package Aheadworks\Giftcard\Pricing\Price
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{
    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $_maximalPrice = null;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $_minimalPrice = null;

    /**
     * Returns max price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaximalPrice()
    {
        if ($this->_maximalPrice === null) {
            $openAmountMax = $this->_getPriceModel()->getOpenAmountMax($this->getProduct());
            $price = false;
            if ($openAmountMax !== false) {
                $price = $openAmountMax;
            }
            $amounts = $this->_getPriceModel()->getAmounts($this->getProduct());
            if (!empty($amounts)) {
                if ($price) {
                    $amounts[] = $price;
                }
                $price = max($amounts);
            }
            if ($price) {
                $this->_maximalPrice = $this->calculator->getAmount(
                    $this->priceCurrency->convertAndRound($price),
                    $this->getProduct()
                );
            }
        }
        return $this->_maximalPrice;
    }

    /**
     * Returns min price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinimalPrice()
    {
        if ($this->_minimalPrice === null) {
            $openAmountMin = $this->_getPriceModel()->getOpenAmountMin($this->getProduct());
            $price = false;
            if ($openAmountMin !== false) {
                $price = $openAmountMin;
            }
            $amounts = $this->_getPriceModel()->getAmounts($this->getProduct());
            if (!empty($amounts)) {
                if ($price) {
                    $amounts[] = $price;
                }
                $price = min($amounts);
            }
            if ($price) {
                $this->_minimalPrice = $this->calculator->getAmount(
                    $this->priceCurrency->convertAndRound($price),
                    $this->getProduct()
                );
            }
        }
        return $this->_minimalPrice;
    }

    /**
     * @return \Aheadworks\Giftcard\Model\Product\Type\Giftcard\Price
     */
    protected function _getPriceModel()
    {
        return $this->getProduct()->getPriceModel();
    }
}
