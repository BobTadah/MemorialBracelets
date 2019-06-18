<?php

namespace MemorialBracelets\NameCategory\Controller\Index;

//use Ved\Mymodule\Model\NewsFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class NameCategory extends Action
{
    protected $resultJsonFactory;
    protected $resultPageFactory;
    protected $layoutFactory;

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

    /** @var $productStatus Status */
    protected $productStatus;

    /** @var $productVisibility Visibility */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * NameCategory constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param SearchCriteriaInterface $searchCriteriaInterface
     * @param FilterBuilder $filterBuilder
     * @param FilterGroup $filterGroup
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ProductRepositoryInterface $productRepository,
        Status $productStatus,
        Visibility $productVisibility,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
        FilterGroupBuilder $filterGroupBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->resultPageFactory  = $resultPageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->productRepository = $productRepository;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->metadataService = $metadataServiceInterface;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        parent::__construct($context);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';

            if ($filter->getField() == 'category_id') {
                $categoryFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($categoryFilter) {
            $collection->addCategoriesFilter($categoryFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        foreach ($this->metadataService->getList($this->searchCriteriaBuilder->create())->getItems() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->getSelect()->orderRand();
        $collection->load();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * This will get the product collection and filter in the products that are have
     * the attribute: [favorite product] set to yes.
     *
     * @param null $categoryId
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getNameProducts($categoryId = null)
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
            ->setField('type_id')
            ->setValue('name')
            ->create();

        $filter4 = $this->filterBuilder
            ->setField('category_id')
            ->setConditionType('eq')
            ->setValue($categoryId)
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
        $filter_group4 = $this->filterGroupBuilder
            ->addFilter($filter4)
            ->create();

        /** apply filters and page size to product repository. */
        $this->searchCriteriaInterface
            ->setFilterGroups([$filter_group1, $filter_group2, $filter_group3, $filter_group4])
            ->setPageSize(1);
        $products = $this->getRandomList($this->searchCriteriaInterface);

        return $products->getItems();
    }


    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $categoryId = $this->getRequest()->getParam('cat');
            $nameItems = $this->getNameProducts($categoryId);
            $_randomProducts = $output = array_slice($nameItems, 0, 1); // the 1 means get just first element
            $_product = $_randomProducts[0];
            $product = $_product;
            $instance = $product->getTypeInstance();
            if ($instance instanceof \MemorialBracelets\NameProduct\Model\Product\Type\Name) {
                $nameInfo =  $instance->getNameListRow($product);
            } else {
                $nameInfo = '<td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>';
            }
            $nameLink = '<td>
                <strong class="product name product-item-name">
                    <a class="product-item-link"
                       href="'.  $_product->getProductUrl() .'">
                        '. $_product->getName() .'
                    </a>
                </strong>
            </td>
            ';
            $nameSelect = '<td>
                            <!--new plp buttons-->
                            <div class="product-item-inner">
                                <div class="buttons">
                                    <a class="button btn button-customize" href="'. $_product->getProductUrl() .'"
                >Select</a>
            </div>
            </div>
            </td>
            ';
            $test = $nameLink . $nameInfo . $nameSelect;
            return $result->setData($test);
        }
    }
}
