<?php

namespace MemorialBracelets\BraceletPreview\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use MemorialBracelets\BraceletPreview\Helper\AddPreviewPieceToResult;
use MemorialBracelets\BraceletPreview\Model\PreviewOptions;
use MemorialBracelets\NameProduct\Model\Product\Type\Name;

/**
 * Class Preview
 *
 * @package MemorialBracelets\BraceletPreview\Block\Product\View\Preview
 */
class Preview extends Template
{
    /** @var Registry */
    protected $coreRegistry;

    /** @var AddPreviewPieceToResult */
    protected $processor;

    /** @var Collection */
    protected $optionCollection;

    /** @var StoreManagerInterface */
    protected $store;

    /**
     * Preview constructor.
     *
     * @param Template\Context        $context
     * @param Registry                $registry
     * @param AddPreviewPieceToResult $processor
     * @param CollectionFactory       $optionCollectionFactory
     * @param array                   $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        AddPreviewPieceToResult $processor,
        CollectionFactory $optionCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->processor = $processor;
        $this->optionCollection = $optionCollectionFactory->create();
        $this->store = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Engraving Options:
     * [Enable Bracelet Preview]
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Engraving Options:
     * [Maximum Number of Engraving lines]
     *
     * @return mixed
     */
    public function getEngravingLines()
    {
        return $this->_scopeConfig->getValue('catalog/engraving/lines', 'store');
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Engraving Options:
     * [Material Color Attribute, Paracord Color Attribute, Top Icon Attribute,
     * Left Charm Attribute, Right Charm Attribute ]
     *
     * @return mixed
     */
    public function getOptionIds()
    {
        $product = $this->getProduct();
        $result = [];

        if ($product->getTypeId() == Name::TYPE_CODE) {
            /** @var Name $type */
            $type = $product->getTypeInstance();
            $collection = $type->getAssociatedProductCollection($product);

            $products = $collection->getItems();
        } else {
            $products = [$product];
        }

        $productIds = array_map(
            function (Product $product) {
                return $product->getId();
            },
            $products
        );

        $options = $this->getProductOptions($productIds);

        /** @var \Magento\Catalog\Model\Product[] $products */
        foreach ($products as $product) {
            if (!isset($options[$product->getId()])) {
                continue;
            }
            foreach ($options[$product->getId()] as $option) {
                $piece = $option->getData('piece', false);
                if ($piece && $piece !== PreviewOptions::PART_NONE) {
                    if (!isset($result[$piece])) {
                        $result[$piece] = [];
                    }
                    $result[$piece][] = $option->getId();
                }
            }
        }
        return $result;
    }

    /**
     * @param $productIds
     *
     * @return array
     */
    protected function getProductOptions($productIds)
    {
        $storeId = $this->store->getStore()->getId();

        $collection = $this->optionCollection->addFieldToFilter('cpe.entity_id', ['in' => $productIds])
            ->addTitleToResult($storeId)
            ->addPriceToResult($storeId);

        $collection = $this->processor->addPieceToResult($collection);

        $collection->addValuesToResult($storeId);

        /** @var Option[] $options */
        $options = $collection->getItems();

        $indexedOptions = [];
        foreach ($options as $option) {
            $indexedOptions[$option->getProductId()][] = $option;
        }

        return $indexedOptions;
    }

    /**
     * This will return the current product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }
}
