<?php

namespace MemorialBracelets\NameProduct\Model\Sales\AdminOrder\Product\Quote\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\AdminOrder\Product\Quote\Initializer as Subject;
use MemorialBracelets\NameProduct\Model\Product\Type\Name;

class Initializer
{
    /**
     * @param Subject    $subject
     * @param callable   $proceed
     * @param Quote      $quote
     * @param Product    $product
     * @param DataObject $config
     *
     * @return \Magento\Quote\Model\Quote\Item|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundInit(
        Subject $subject,
        \Closure $proceed,
        Quote $quote,
        Product $product,
        DataObject $config
    ) {
        $item = $proceed($quote, $product, $config);

        if (is_string($item) && $product->getTypeId() != Name::TYPE_CODE) {
            $item = $quote->addProduct(
                $product,
                $config,
                AbstractType::PROCESS_MODE_LITE
            );
        }
        return $item;
    }
}
