<?php

namespace MemorialBracelets\NameProduct\Helper\Product\Configuration\Plugin;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use MemorialBracelets\NameProduct\Model\Product\Type\Name as Type;

class Name
{
    /**
     * Retrieves grouped product options list
     *
     * @param Configuration $subject
     * @param \Closure      $proceed
     * @param ItemInterface $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundGetOptions(Configuration $subject, \Closure $proceed, ItemInterface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId != Type::TYPE_CODE) {
            return $proceed($item);
        }

        $options = [];

        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $associatedProducts = $typeInstance->getConfiguredProducts($product);

        if ($associatedProducts) {
            foreach ($associatedProducts as $associatedProduct) {
                $qty = $item->getOptionByCode('nameassociated_product_' . $associatedProduct->getId());
                $options[] = [
                    'label' => $associatedProduct->getName(),
                    'value' => $qty && $qty->getValue() ? $qty->getValue() : 0,
                ];
            }
        }

        $options = array_merge($options, $proceed($item));
        $isUnConfigured = true;
        foreach ($options as &$option) {
            if ($option['value']) {
                $isUnConfigured = false;
                break;
            }
        }

        return $isUnConfigured ? [] : $options;
    }
}
