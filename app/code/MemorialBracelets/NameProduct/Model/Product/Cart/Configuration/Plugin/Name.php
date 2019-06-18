<?php

namespace MemorialBracelets\NameProduct\Model\Product\Cart\Configuration\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CartConfiguration;
use MemorialBracelets\NameProduct\Model\Product\Type\Name as Type;

class Name
{
    /**
     * Decide whether product has been configured for cart or not
     *
     * @param CartConfiguration $subject
     * @param \Closure          $proceed
     * @param Product           $product
     * @param                   $config
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsProductConfigure(CartConfiguration $subject, \Closure $proceed, Product $product, $config)
    {
        if ($product->getTypeId() == Type::TYPE_CODE) {
            return isset($config['super_group']);
        }

        return $proceed($product, $config);
    }
}
