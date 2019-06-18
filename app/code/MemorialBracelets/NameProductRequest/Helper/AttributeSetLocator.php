<?php

namespace MemorialBracelets\NameProductRequest\Helper;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product;

class AttributeSetLocator
{
    /** @var SearchCriteriaBuilderFactory  */
    private $searchBuilderFactory;

    /** @var AttributeSetRepositoryInterface  */
    private $eavSetRepository;

    /**
     * @param AttributeSetRepositoryInterface $eavSetRepository
     * @param SearchCriteriaBuilderFactory $searchBuilderFactory
     */
    public function __construct(
        AttributeSetRepositoryInterface $eavSetRepository,
        SearchCriteriaBuilderFactory $searchBuilderFactory
    ) {
        $this->eavSetRepository = $eavSetRepository;
        $this->searchBuilderFactory = $searchBuilderFactory;
    }

    /**
     * Get the attribute set id for name products
     * @return int
     * @throws LocalizedException
     */
    public function locate()
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->searchBuilderFactory->create();
        $criteriaBuilder->addFilter('entity_type_code', Product::ENTITY);
        $criteria = $criteriaBuilder->create();

        $results = $this->eavSetRepository->getList($criteria);
        foreach ($results->getItems() as $item) {
            if ($item->getAttributeSetName() == 'Name Product') {
                return $item->getAttributeSetId();
            }
        }

        throw new LocalizedException(__('Could not find "Name Product" attribute set for Product Request'));
    }

    /**
     * Get the attribute set id for name products (special request version)
     * @return int
     * @throws LocalizedException
     */
    public function locateSpecialRequest()
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->searchBuilderFactory->create();
        $criteriaBuilder->addFilter('entity_type_code', Product::ENTITY);
        $criteria = $criteriaBuilder->create();

        $results = $this->eavSetRepository->getList($criteria);
        foreach ($results->getItems() as $item) {
            if ($item->getAttributeSetName() == 'Special Request') {
                return $item->getAttributeSetId();
            }
        }

        throw new LocalizedException(__('Could not find "Special Request" attribute set for Product Request'));
    }
}
