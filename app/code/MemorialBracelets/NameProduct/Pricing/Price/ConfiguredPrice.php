<?php

namespace MemorialBracelets\NameProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice as OriginalFinalPrice;
use Magento\Wishlist\Model\Item\Option;
use MemorialBracelets\NameProduct\Model\Product\Type\Name;

class ConfiguredPrice extends OriginalFinalPrice implements ConfiguredPriceInterface
{
    const PRICE_CODE = self::CONFIGURED_PRICE_CODE;

    protected $item;

    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    protected function calculatePrice()
    {
        $value = 0.;
        /** @var Name $typeInstance */

        $typeInstance = $this->getProduct()->getTypeInstance();
        $associatedProducts = $typeInstance
            ->setStoreFilter($this->getProduct()->getStore(), $this->getProduct())
            ->setAssociatedProducts($this->getProduct());

        foreach ($associatedProducts as $product) {
            /**
             * @var Product $product
             * @var Option $customOption
             */
            $customOption = $this->getProduct()
                ->getCustomOption('nameassociated_product_' . $product->getId());

            if (!$customOption) {
                continue;
            }

            $finalPrice = $product->getPriceInfo()
                ->getPrice(OriginalFinalPrice::PRICE_CODE)
                ->getValue();

            $value += $finalPrice * ($customOption->getValue() ? $customOption->getValue() : 1);
        }
        return $value;
    }

    /**
     * @return bool|float
     */
    public function getValue()
    {
        return $this->item ? $this->calculatePrice() : parent::getValue();
    }
}
