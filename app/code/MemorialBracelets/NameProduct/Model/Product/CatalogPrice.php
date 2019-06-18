<?php

namespace MemorialBracelets\NameProduct\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CatalogPriceInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class CatalogPrice implements CatalogPriceInterface
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var Product\CatalogPrice */
    protected $commonPriceModel;

    /**
     * CatalogPrice constructor.
     * @param StoreManagerInterface $storeManager
     * @param Product\CatalogPrice  $commonPriceModel
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Product\CatalogPrice $commonPriceModel
    ) {
        $this->storeManager = $storeManager;
        $this->commonPriceModel = $commonPriceModel;
    }

    /** @inheritdoc */
    public function getCatalogPrice(
        Product $product,
        StoreInterface $store = null,
        $inclTax = false
    ) {
        // Avoid loading stock status by admin's website
        if ($store instanceof StoreInterface) {
            $currentStore = $this->storeManager->getStore();
            $this->storeManager->setCurrentStore($store->getId());
        }

        $subProducts = $product->getTypeInstance()->getConfiguredProducts($product);

        // Return the current store
        if ($store instanceof StoreInterface) {
            $this->storeManager->setCurrentStore($currentStore->getId());
        }

        if (!$subProducts) {
            return null;
        }

        $minPrice = null;
        /** @var Product $subProduct */
        foreach ($subProducts as $subProduct) {
            $subProduct->setWebsiteId($product->getWebsiteId())->setCustomerGroupId($product->getCustomerGroupId());
            if ($subProduct->isSalable()) {
                if ($this->commonPriceModel->getCatalogPrice($subProduct) < $minPrice || is_null($minPrice)) {
                    $minPrice = $this->commonPriceModel->getCatalogPrice($subProduct);
                    $product->setTaxClassId($subProduct->getTaxClassId());
                }
            }
        }

        return $minPrice;
    }

    /** @inheritdoc */
    public function getCatalogRegularPrice(Product $product)
    {
        return null;
    }
}
