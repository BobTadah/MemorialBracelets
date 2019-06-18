<?php

namespace MemorialBracelets\NameProductRequest\Model\Config\Source;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Option\ArrayInterface;

class Simples extends AbstractSource implements ArrayInterface
{
    /** @var ProductRepositoryInterface */
    private $repository;

    /** @var SearchCriteriaBuilderFactory */
    private $builderFactory;

    /** @var ProductInterface[] */
    private $products;

    /**
     * @param ProductRepositoryInterface $repository
     * @param SearchCriteriaBuilderFactory $builderFactory
     */
    public function __construct(ProductRepositoryInterface $repository, SearchCriteriaBuilderFactory $builderFactory)
    {
        $this->repository = $repository;
        $this->builderFactory = $builderFactory;

        $this->loadProducts();
    }

    /**
     * Load all products
     */
    private function loadProducts()
    {
        $searchBuilder = $this->builderFactory->create();
        $searchBuilder->addFilter(ProductInterface::TYPE_ID, 'simple');
        $criteria = $searchBuilder->create();

        $result = $this->repository->getList($criteria);

        $this->products = $result->getItems();
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return array_map(
            function (ProductInterface $product) {
                return ['value' => $product->getSku(), 'label' => $product->getName()];
            },
            $this->products
        );
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array('value' => 'label')
     */
    public function toArray()
    {
        return array_reduce(
            $this->products,
            function ($carry, ProductInterface $product) {
                $carry[$product->getSku()] = $product->getName();
                return $carry;
            },
            []
        );
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
