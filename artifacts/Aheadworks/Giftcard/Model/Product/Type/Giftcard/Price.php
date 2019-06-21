<?php
namespace Aheadworks\Giftcard\Model\Product\Type\Giftcard;

/**
 * Class Price
 * @package Aheadworks\Giftcard\Model\Product\Type\Giftcard
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    const ALLOW_OPEN_AMOUNT_ATTR_CODE = 'aw_gc_allow_open_amount';
    const OPEN_AMOUNT_MIN_ATTR_CODE = 'aw_gc_open_amount_min';
    const OPEN_AMOUNT_MAX_ATTR_CODE = 'aw_gc_open_amount_max';
    const AMOUNTS_ATTR_CODE = 'aw_gc_amounts';

    /**
     * Get base price with apply Gift Card amount
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param float|null $qty
     *
     * @return float
     */
    public function getBasePrice($product, $qty = null)
    {
        return $this->_applyAmounts($product, (float) $product->getPrice());
    }

    /**
     * Apply Gift Card amounts for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param $price
     */
    protected function _applyAmounts(\Magento\Catalog\Model\Product $product, $price)
    {
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('aw_gc_amounts');
            if ($customOption) {
                $price += $customOption->getValue();
            }
        }
        return $price;
    }

    public function getOpenAmountMin(\Magento\Catalog\Model\Product $product)
    {
        if ($this->_getAttribute($product, self::ALLOW_OPEN_AMOUNT_ATTR_CODE)) {
            return (float)$this->_getAttribute($product, self::OPEN_AMOUNT_MIN_ATTR_CODE);
        }
        return false;
    }

    public function getOpenAmountMax(\Magento\Catalog\Model\Product $product)
    {
        if ($this->_getAttribute($product, self::ALLOW_OPEN_AMOUNT_ATTR_CODE)) {
            return (float)$this->_getAttribute($product, self::OPEN_AMOUNT_MAX_ATTR_CODE);
        }
        return false;
    }

    public function getAmounts(\Magento\Catalog\Model\Product $product)
    {
        return $product->getTypeInstance()->getAmounts($product);
    }

    protected function _getAttribute(\Magento\Catalog\Model\Product $product, $code)
    {
        if (!$product->hasData($code)) {
            $product->getResource()->load($product, $product->getId());
        }
        return $product->getData($code);
    }
}
