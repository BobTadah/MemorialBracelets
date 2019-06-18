<?php

namespace MemorialBracelets\NameProduct\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CopyConstructorInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection as LinkCollection;
use MemorialBracelets\NameProduct\Model\ResourceModel\Product\Link;

class Name implements CopyConstructorInterface
{
    /**
     * @param Product $product
     * @return LinkCollection
     */
    protected function getNameLinkCollection(Product $product)
    {
        /** @var Product\Link $links */
        $links = $product->getLinkInstance();
        $links->setLinkTypeId(Link::LINK_TYPE_NAME);

        return $links->getLinkCollection()
            ->setProduct($product)
            ->addLinkTypeIdFilter()
            ->addProductIdFilter()
            ->joinAttributes();
    }

    /**
     * Build product duplicate
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate)
    {
        if ($product->getTypeId() !== Link::LINK_TYPE_NAME) {
            return;
        }

        $data = [];
        $attributes = [];
        $link = $product->getLinkInstance();
        $link->setLinkTypeId(Link::LINK_TYPE_NAME);
        foreach ($link->getAttributes() as $attribute) {
            if (isset($attribute['code'])) {
                $attributes[] = $attribute['code'];
            }
        }

        /** @var Product\Link $link */
        foreach ($this->getNameLinkCollection($product) as $link) {
            $data[$link->getLinkedProductId()] = $link->toArray($attributes);
        }
        $duplicate->setNameLinkData($data);
    }
}
