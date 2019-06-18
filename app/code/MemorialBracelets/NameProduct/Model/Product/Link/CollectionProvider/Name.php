<?php

namespace MemorialBracelets\NameProduct\Model\Product\Link\CollectionProvider;

use Magento\Catalog\Model\ProductLink\CollectionProviderInterface;

class Name implements CollectionProviderInterface
{
    /** @inheritdoc */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product)
    {
        $instance = $product->getTypeInstance();
        if ($instance instanceof \MemorialBracelets\NameProduct\Model\Product\Type\Name) {
            return $instance->getConfiguredProducts($product);
        } else {
            return [];
        }
    }
}
