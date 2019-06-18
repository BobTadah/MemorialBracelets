<?php

namespace MemorialBracelets\NameProduct\Model\Product\Type\Name;

use Magento\Catalog\Model\Product;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    public function getFinalPrice($qty, $product)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = parent::getFinalPrice($qty, $product);
        if ($product->hasCustomOptions()) {
            /** @var \MemorialBracelets\NameProduct\Model\Product\Type\Name $typeInstance */
            $typeInstance = $product->getTypeInstance();

            $associatedProducts = $typeInstance->setStoreFilter($product->getStore(), $product)
                ->getConfiguredProducts($product);

            foreach ($associatedProducts as $childProduct) {
                /** @var Product $childProduct */
                $option = $product->getCustomOption('nameassociated_product_'. $childProduct->getId());
                if (!$option) {
                    continue;
                }
                $childQty = $option->getValue();
                if (!$childQty) {
                    continue;
                }
                $finalPrice += $childProduct->getFinalPrice($childQty) * $childQty;
            }
        }

        $product->setFinalPrice($finalPrice);

        return max(0, $product->getData('final_price'));
    }
}
