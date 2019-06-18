<?php

namespace MemorialBracelets\NameProduct\Model\Product\Type\Name;

use MemorialBracelets\NameProduct\Model\Product\Type\Name;

class Backend extends Name
{
    /**
     * No filters required in backend
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSaleableStatus($product)
    {
        return $this;
    }
}
