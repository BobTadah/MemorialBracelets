<?php

namespace MemorialBracelets\NameProductRequest\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;

class StockSetter
{
    /** @var StockStatusInterfaceFactory  */
    private $stockStatusFactory;

    /** @var StockStatusRepositoryInterface  */
    private $stockStatusRepository;

    /** @var StockStatusCriteriaInterfaceFactory  */
    private $stockStatusSearchFactory;

    /** @var StockItemInterfaceFactory */
    private $stockItemFactory;

    /** @var StockItemRepositoryInterface */
    private $stockItemRepository;

    /** @var StockItemCriteriaInterfaceFactory */
    private $stockItemSearchFactory;

    /**
     * @param StockStatusInterfaceFactory $stockStatusFactory
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockStatusCriteriaInterfaceFactory $stockStatusSearchFactory
     * @param StockItemInterfaceFactory $stockItemFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $stockItemSearchFactory
     */
    public function __construct(
        StockStatusInterfaceFactory $stockStatusFactory,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusCriteriaInterfaceFactory $stockStatusSearchFactory,
        StockItemInterfaceFactory $stockItemFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemSearchFactory
    ) {
        $this->stockStatusFactory = $stockStatusFactory;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->stockStatusSearchFactory = $stockStatusSearchFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemSearchFactory = $stockItemSearchFactory;
    }

    /**
     * Setup the stock for a new name product
     * @param ProductInterface $product
     */
    public function setupForProduct(ProductInterface $product)
    {
        $this->setupStockItemForProduct($product);
        $this->setupStockStatusForProduct($product);
    }

    /**
     * Setup the Stock Status for a new name product
     * @param ProductInterface $product
     */
    public function setupStockStatusForProduct(ProductInterface $product)
    {
        /** @var StockStatusCriteriaInterface $criteria */
        $criteria = $this->stockStatusSearchFactory->create();
        $criteria->setProductsFilter($product->getId());

        $result = $this->stockStatusRepository->getList($criteria);

        if ($result->getTotalCount() > 0) {
            $stockResults = $result->getItems();
            $stock = reset($stockResults);
        } else {
            /** @var StockStatusInterface $stock */
            $stock = $this->stockStatusFactory->create();
            $stock->setProductId($product->getId());
        }

        $stock->setStockStatus(1);
        $stock->setStockId(1);
        $this->stockStatusRepository->save($stock);
    }

    /**
     * Setup the Stock Item for a new name product
     * @param ProductInterface $product
     */
    public function setupStockItemForProduct(ProductInterface $product)
    {
        /** @var StockItemCriteriaInterface $criteria */
        $criteria = $this->stockItemSearchFactory->create();
        $criteria->setProductsFilter($product->getId());

        $result = $this->stockItemRepository->getList($criteria);

        if ($result->getTotalCount() > 0) {
            $stockResults = $result->getItems();
            $stock = reset($stockResults);
        } else {
            /** @var StockItemInterface $stock */
            $stock = $this->stockItemFactory->create();
            $stock->setProductId($product->getId());
        }

        $stock->setUseConfigManageStock(false);
        $stock->setManageStock(false);
        $stock->setQty(1);
        $stock->setIsInStock(true);
        $this->stockItemRepository->save($stock);
    }
}
