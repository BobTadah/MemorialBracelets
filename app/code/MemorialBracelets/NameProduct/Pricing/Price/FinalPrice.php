<?php

namespace MemorialBracelets\NameProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\Catalog\Pricing\Price\FinalPriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;

class FinalPrice extends CatalogFinalPrice implements FinalPriceInterface
{
    const PRICE_CODE = 'final_price';

    protected $minProduct;

    public function getValue()
    {
        return $this->getMinProduct()->getPriceInfo()->getPrice(static::PRICE_CODE)->getValue();
    }

    /**
     * @return Product
     */
    public function getMinProduct()
    {
        if (is_null($this->minProduct)) {
            $products = $this->product->getTypeInstance()->getConfiguredProducts($this->product);
            $minPrice = null;
            foreach ($products as $item) {
                $product = clone $item;
                $product->setQty(PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT);
                $price = $product->getPriceInfo()
                    ->getPrice(self::PRICE_CODE)
                    ->getValue();

                if ($price !== false && ($price <= (is_null($minPrice) ? $price : $minPrice))) {
                    $this->minProduct = $product;
                    $minPrice = $price;
                }
            }
        }
        return $this->minProduct;
    }
}
