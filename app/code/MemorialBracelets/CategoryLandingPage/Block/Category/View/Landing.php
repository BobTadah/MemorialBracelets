<?php

namespace MemorialBracelets\CategoryLandingPage\Block\Category\View;

use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Zend_Db_Expr;

/**
 * Class Landing
 * @package MemorialBracelets\CategoryLandingPage\Block\Category\View
 */
class Landing extends Template
{
    /** @var Registry */
    protected $registry;

    /** @var Output */
    protected $outputHelper;

    /** @var Category */
    protected $categoryFactory;
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param Template\Context $context
     * @param Registry $registry
     * @param Output $outputHelper
     * @param CategoryFactory $categoryFactory
     * @param ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        Output $outputHelper,
        CategoryFactory $categoryFactory,
        ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->outputHelper = $outputHelper;
        $this->categoryFactory = $categoryFactory;
        $this->resource = $resource;
    }

    /**
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * @param int $categoryId
     * @return Category
     */
    public function fetchCategory($categoryId)
    {
        $category = $this->categoryFactory->create();
        $category->getResource()->load($category, $categoryId);
        return $category;
    }

    /**
     * @return array|Category[]|\Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getSubCategories()
    {
        $category = $this->getCurrentCategory();
        if (!$category) {
            return [];
        }

        return $category->getChildrenCategories();
    }

    /**
     * @return array|Category[]
     */
    public function getActiveSubCategories()
    {
        $categories = $this->getSubCategories();
        if ($categories instanceof Collection) {
            $categories = $categories->getItems();
        }

        return array_filter(
            $categories,
            function (Category $category) {
                return $category->getIsActive();
            }
        );
    }

    /**
     * @return Output
     */
    public function outputHelper()
    {
        return $this->outputHelper;
    }


    /**
     * Repository Approach too Slow for Product Count
     * TODO: Add Support for Multiple Stores
     *
     * @param Category $category
     * @return int
     */
    public function getVisibleProductCount(Category$category)
    {
        $productTable = $this->resource->getTableName('catalog_category_product');

        $select = $this->resource->getConnection()->select()->from(
            ['main_table' => $productTable],
            [new Zend_Db_Expr('COUNT(main_table.product_id)')]
        )->joinInner(
            ['cpf1' => 'catalog_product_flat_1'],
            'main_table.product_id = cpf1.entity_id AND (cpf1.visibility = 4 OR cpf1.visibility = 2)'
        )->where(
            'main_table.category_id = :category_id'
        );

        $bind = ['category_id' => (int) $category->getId()];
        $counts = $this->resource->getConnection()->fetchOne($select, $bind);

        return (int) $counts;
    }
}
