<?php

namespace MemorialBracelets\FavoriteProducts\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Wishlist\Helper\Data as Wishlist;
use Magento\Framework\Pricing\Helper\Data as Pricing;

/**
 * Class Favorites
 *
 * @package MemorialBracelets\FavoriteProducts\Block
 */
class Favorites extends Template
{
    /** @var $filterBuilder FilterBuilder */
    protected $filterBuilder;

    /** @var $searchCriteriaInterface SearchCriteriaInterface */
    protected $searchCriteriaInterface;

    /** @var $filterGroup FilterGroup */
    protected $filterGroup;

    /** @var $filterGroupBuilder FilterBuilder */
    protected $filterGroupBuilder;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var Registry  */
    protected $coreRegistry;

    /** @var $productStatus Status */
    protected $productStatus;

    /** @var $productVisibility Visibility */
    protected $productVisibility;

    /** @var $compareHelper Compare */
    protected $compareHelper;

    /** @var $imageHelper Image */
    protected $imageHelper;

    /** @var $pricingHelper Pricing */
    protected $pricingHelper;

    /** @var$wishlistHelper Wishlist */
    protected $wishlistHelper;

    /**
     * Favorites constructor.
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param Image $imageHelper
     * @param Compare $compareHelper
     * @param Wishlist $wishlistHelper
     * @param Pricing $pricingHelper
     * @param SearchCriteriaInterface $searchCriteriaInterface
     * @param FilterBuilder $filterBuilder
     * @param FilterGroup $filterGroup
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        Status $productStatus,
        Visibility $productVisibility,
        Image $imageHelper,
        Compare $compareHelper,
        Wishlist $wishlistHelper,
        Pricing $pricingHelper,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
        FilterGroupBuilder $filterGroupBuilder,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->coreRegistry = $registry;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->imageHelper = $imageHelper;
        $this->compareHelper = $compareHelper;
        $this->wishlistHelper = $wishlistHelper;
        $this->pricingHelper = $pricingHelper;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
        $this->filterGroupBuilder = $filterGroupBuilder;
        parent::__construct($context, $data);
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Home Page Favorite Options:
     * [Enabled]
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue('catalog/home_favorite/enabled', 'store');
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Home Page Favorite Options:
     * [Count]
     *
     * @return mixed
     */
    public function getProductCount()
    {
        return $this->_scopeConfig->getValue('catalog/home_favorite/count', 'store');
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Home Page Favorite Options:
     * [Title]
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->_scopeConfig->getValue('catalog/home_favorite/title', 'store');
    }

    /**
     * This will get the product collection and filter in the products that are have
     * the attribute: [favorite product] set to yes.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProducts()
    {
        /** create all the product filters. */
        $filter1 = $this->filterBuilder
            ->setField('status')
            ->setConditionType('in')
            ->setValue($this->productStatus->getVisibleStatusIds())
            ->create();

        $filter2 = $this->filterBuilder
            ->setField('visibility')
            ->setConditionType('in')
            ->setValue($this->productVisibility->getVisibleInSiteIds())
            ->create();

        $filter3 = $this->filterBuilder
            ->setField('favorite_product')
            ->setValue(1)
            ->create();

        /* Add each filter to its own filter group.  It seems that filters
           are added as OR comparisons, filter groups as AND comparisons
           we want products that are (visible AND enabled AND favorite) */
        $filter_group1 = $this->filterGroupBuilder
            ->addFilter($filter1)
            ->create();
        $filter_group2 = $this->filterGroupBuilder
            ->addFilter($filter2)
            ->create();
        $filter_group3 = $this->filterGroupBuilder
            ->addFilter($filter3)
            ->create();

        /** apply filters and page size to product repository. */
        $this->searchCriteriaInterface
            ->setFilterGroups([$filter_group1, $filter_group2, $filter_group3])
            ->setPageSize($this->getProductCount());
        $products = $this->productRepository->getList($this->searchCriteriaInterface);

        return $products->getItems();
    }

    /**
     * This function will return the Compare Helper Class.
     *
     * @return Compare
     */
    public function getCompareHelper()
    {
        return $this->compareHelper;
    }

    /**
     * This function will return the Image Helper Class.
     *
     * @return Image
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * This function will return the Wishlist Helper Class.
     *
     * @return Wishlist
     */
    public function getWishlistHelper()
    {
        return $this->wishlistHelper;
    }

    /**
     * This function will return the Price Helper Class.
     *
     * @return Pricing
     */
    public function getPriceHelper()
    {
        return$this->pricingHelper;
    }
}
