<?php

namespace MemorialBracelets\NameProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin;

use Magento\Catalog\Api\Data\ProductLinkExtensionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use MemorialBracelets\NameProduct\Model\Product\Type\Name as Type;

class Name
{
    const TYPE_NAME = 'name_associated';

    protected $linkFactory;

    protected $repository;

    protected $linkExtensionFactory;

    public function __construct(
        ProductLinkInterfaceFactory $linkFactory,
        ProductRepositoryInterface $repository,
        ProductLinkExtensionFactory $linkExtensionFactory
    ) {
        $this->linkFactory = $linkFactory;
        $this->repository = $repository;
        $this->linkExtensionFactory = $linkExtensionFactory;
    }

    /**
     * @param ProductLinks $subject
     * @param Product      $product
     * @param array        $links
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function beforeInitializeLinks(ProductLinks $subject, Product $product, array $links)
    {
        if ($product->getTypeId() !== Type::TYPE_CODE || $product->getNameReadonly()) {
            // Exit out early
            return;
        }

        $links = isset($links[self::TYPE_NAME]) ? $links[self::TYPE_NAME] : $product->getNameLinkData();
        if (!is_array($links)) {
            $links = [];
        }
        if ($product->getNameLinkData()) {
            $links = array_merge($links, $product->getNameLinkData());
        }
        $newLinks = [];
        $existingLinks = $product->getProductLinks();

        foreach ($links as $linkRaw) {
            /** @var ProductLinkInterface $productLink */
            $productLink = $this->linkFactory->create();
            if (!isset($linkRaw['id'])) {
                continue;
            }
            $productId = $linkRaw['id'];
            $linkedProduct = $this->repository->getById($productId);

            $productLink->setSku($product->getSku())
                ->setLinkType(self::TYPE_NAME)
                ->setLinkedProductSku($linkedProduct->getSku())
                ->setLinkedProductType($linkedProduct->getTypeId())
                ->setPosition($linkRaw['position']);

            if (isset($linkRaw['custom_attributes'])) {
                $productLinkExtension = $productLink->getExtensionAttributes();
                if (is_null($productLinkExtension)) {
                    $productLinkExtension = $this->linkExtensionFactory->create();
                }
                foreach ($linkRaw['custom_attributes'] as $option) {
                    $name = $option['attribute_code'];
                    $value = $option['value'];
                    $setterName = 'set' . ucfirst($name);
                    if (method_exists($productLinkExtension, $setterName)) {
                        call_user_func([$productLinkExtension, $setterName], $value);
                    }
                }
                $productLink->setExtensionAttributes($productLinkExtension);
            }
            $newLinks[] = $productLink;
        }

        $existingLinks = $this->removeUnExistingLinks($existingLinks, $newLinks);

        $links = array_merge($existingLinks, $newLinks);
        $product->setProductLinks(array_merge($existingLinks, $newLinks));
    }

    /**
     * @param array $existingLinks
     * @param array $newLinks
     * @return array
     */
    private function removeUnExistingLinks($existingLinks, $newLinks)
    {
        $result = [];
        foreach ($existingLinks as $key => $link) {
            $result[$key] = $link;
            if ($link->getLinkType() == self::TYPE_NAME) {
                $exists = false;
                foreach ($newLinks as $newLink) {
                    if ($link->getLinkedProductSku() == $newLink->getLinkedProductSku()) {
                        $exists = true;
                    }
                }
                if (!$exists) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }
}
