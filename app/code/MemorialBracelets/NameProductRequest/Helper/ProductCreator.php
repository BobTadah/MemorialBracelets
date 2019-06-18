<?php

namespace MemorialBracelets\NameProductRequest\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\CustomAttributesDataInterfaceFactory;
use MemorialBracelets\NameProduct\Model\Product\Type\Name as NameProductType;
use Ramsey\Uuid\Uuid;

class ProductCreator
{
    /** @var ProductInterfaceFactory */
    private $productFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var AttributeSetLocator  */
    private $attributeSetLocator;

    /** @var SimpleProductLinker */
    private $simpleProductLinker;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeSetLocator $attributeSetLocator
     * @param SimpleProductLinker $simpleProductLinker
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        AttributeSetLocator $attributeSetLocator,
        SimpleProductLinker $simpleProductLinker
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->attributeSetLocator = $attributeSetLocator;
        $this->simpleProductLinker = $simpleProductLinker;
    }

    /**
     * Generate a new name product
     * @return ProductInterface
     */
    public function create()
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->create();

        $product->setTypeId(NameProductType::TYPE_CODE);
        $product->setSku($this->generateSku());
        $product->setVisibility(4);
        $product->setAttributeSetId($this->attributeSetLocator->locateSpecialRequest());
        $product->setStatus(Status::STATUS_DISABLED);
        $product->setCustomAttribute('category_ids', $this->getCategoryIds());

        $this->simpleProductLinker->link($product);

        return $product;
    }

    /**
     * Set the URLKey of a Product to be it's name plus a base64 encoded timestamp
     * @param ProductInterface $product
     * @return ProductInterface
     */
    public function setUrlKey(ProductInterface $product)
    {
        $key = $product->getName() . ' ' . base_convert(microtime(true), 10, 32);
        $product->setCustomAttribute('url_key', $key);
        return $product;
    }

    /**
     * Save a product
     * @param ProductInterface $product
     * @return ProductInterface
     */
    public function save(ProductInterface $product)
    {
        return $this->productRepository->save($product);
    }

    /**
     * Get the Category IDs to add all requested products to
     * @todo Convert to admin setting
     * @return string[]|int[]
     */
    private function getCategoryIds()
    {
        return ['107'];
    }

    /**
     * Generate a sku for a name product
     * @return string
     */
    private function generateSku()
    {
        return 'SR' . Uuid::uuid4()->toString();
    }
}
