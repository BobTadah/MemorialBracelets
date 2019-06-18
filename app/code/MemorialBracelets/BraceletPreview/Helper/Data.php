<?php

namespace MemorialBracelets\BraceletPreview\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as OptionValueFactory;
use Magento\Store\Model\StoreManagerInterface;
use MemorialBracelets\BraceletPreview\Model\PreviewOptions;
use MemorialBracelets\EngravingDisplay\Block\Product\View\Options\Type\Engraving;
use MemorialBracelets\NameProduct\Model\Product\Type\Name;
use MemorialBracelets\BraceletPreview\Helper\AddPreviewPieceToResult;
use Magento\Framework\App\Helper\Context;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;
use MemorialBracelets\CharmOption\Model\CharmOption\IconStorageConfiguration as CharmStorage;
use MemorialBracelets\SwatchOption\Helper\ImageStorageConfiguration as SwatchStorage;
use MemorialBracelets\IconOption\Helper\IconStorageConfiguration as IconStorage;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as OptionCollection;
use MemorialBracelets\SwatchOption\Helper\AddSwatchDataToResult;

/**
 * Class Data
 * @package MemorialBracelets\BraceletPreview\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var AddPreviewPieceToResult */
    protected $processor;

    /** @var Collection */
    protected $optionCollection;

    /** @var ProductRepositoryInterface */
    protected $productRepo;

    /** @var CharmOptionRepositoryInterface */
    protected $charmRepo;

    /** @var CharmStorage */
    protected $charmStorage;

    /** @var IconStorage */
    protected $iconStorage;

    /** @var SwatchStorage */
    protected $swatchStorage;

    /** @var IconOptionRepositoryInterface */
    protected $iconRepo;

    /** @var LoggerInterface */
    protected $logger;

    /** @var $filterBuilder FilterBuilder */
    protected $filterBuilder;

    /** @var SearchCriteriaInterface */
    protected $searchCriteriaInterface;

    /** @var FilterGroup */
    protected $filterGroup;

    /** @var FilterBuilder */
    protected $filterGroupBuilder;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**@var OptionCollection */
    protected $pOptionCollection;

    /** @var AddSwatchDataToResult */
    protected $swatchAddition;

    /** @var OptionValueFactory */
    protected $optionValueFactory;

    /** attribute code values: */
    const MATERIAL = 'material_color';
    const LEFT_CHARM = 'charm_left';
    const RIGHT_CHARM = 'charm_right';
    const LEFT_ICON = 'icon_left';
    const TOP_ICON = 'icon_top';
    const RIGHT_ICON = 'icon_right';
    const ENGRAVING = 'engraving';
    /**
     * @var Engraving
     */
    private $engraving;

    private $cartProd;

    /**
     * Data constructor.
     * @param Context $context
     * @param \MemorialBracelets\BraceletPreview\Helper\AddPreviewPieceToResult $processor
     * @param CollectionFactory $optionCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepo
     * @param CharmOptionRepositoryInterface $charmRepo
     * @param CharmStorage $charmStorage
     * @param IconStorage $iconStorage
     * @param SwatchStorage $swatchStorage
     * @param IconOptionRepositoryInterface $iconRepo
     * @param SearchCriteriaInterface $searchCriteriaInterface
     * @param FilterBuilder $filterBuilder
     * @param FilterGroup $filterGroup
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param OptionCollection $pOptionCollection
     * @param AddSwatchDataToResult $swatchAddition
     * @param OptionValueFactory $optionValueFactory
     */
    public function __construct(
        Context $context,
        AddPreviewPieceToResult $processor,
        CollectionFactory $optionCollectionFactory,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepo,
        CharmOptionRepositoryInterface $charmRepo,
        CharmStorage $charmStorage,
        IconStorage $iconStorage,
        SwatchStorage $swatchStorage,
        IconOptionRepositoryInterface $iconRepo,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
        FilterGroupBuilder $filterGroupBuilder,
        OptionCollection $pOptionCollection,
        AddSwatchDataToResult $swatchAddition,
        OptionValueFactory $optionValueFactory,
        Engraving $engraving
    ) {
        $this->processor               = $processor;
        $this->optionCollection        = $optionCollectionFactory->create();
        $this->storeManager            = $storeManager;
        $this->productRepo             = $productRepo;
        $this->charmRepo               = $charmRepo;
        $this->iconRepo                = $iconRepo;
        $this->charmStorage            = $charmStorage;
        $this->iconStorage             = $iconStorage;
        $this->swatchStorage           = $swatchStorage;
        $this->logger                  = $context->getLogger();
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterBuilder           = $filterBuilder;
        $this->filterGroup             = $filterGroup;
        $this->filterGroupBuilder      = $filterGroupBuilder;
        $this->pOptionCollection       = $pOptionCollection;
        $this->swatchAddition          = $swatchAddition;
        $this->optionValueFactory      = $optionValueFactory;
        parent::__construct($context);
        $this->engraving = $engraving;
    }

    /**
     * this is the main function that will build and return the preview array and then format the html.
     * @param $item
     * @param $cartOptions
     * @return string
     */
    public function buildPreview($item, $cartOptions)
    {
        $html = '';

        if ($item->getProductId()) {
            try {
                $this->cartProd = $product = $this->getPreviewProduct($item->getProductId());
                $optionArray    = $this->getOptionIds($product);
                $previewArray   = $this->mapOptions($optionArray, $cartOptions);
                $html           = $this->getHtml($previewArray);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                // use this line to output the error message
                $html = $e->getMessage();
            }
        }

        return $html;
    }

    /**
     * @param $product
     * @return array
     */
    protected function getOptionIds($product)
    {
        $result = [];

        if ($product->getTypeId() == Name::TYPE_CODE) {
            /** @var Name $type */
            $type       = $product->getTypeInstance();
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
     * this function will load and return a product by id.
     * @param $id
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function getPreviewProduct($id)
    {
        return $this->productRepo->getById($id);
    }

    /**
     * @param $productIds
     * @return array
     */
    protected function getProductOptions($productIds)
    {
        $storeId = $this->storeManager->getStore()->getId();

        $this->optionCollection->reset();

        $collection = $this->optionCollection->addFieldToFilter('cpe.entity_id', ['in' => $productIds]);

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
     * this function will map the cart item options to the preview array and call the populate function.
     * @param $productOptions
     * @param $cartOptions
     * @return array
     */
    protected function mapOptions($productOptions, $cartOptions)
    {
        // array of all possibilities.
        $previewArray = [
            static::MATERIAL    => null,
            static::LEFT_CHARM  => null,
            static::RIGHT_CHARM => null,
            static::LEFT_ICON   => null,
            static::TOP_ICON    => null,
            static::RIGHT_ICON  => null,
            static::ENGRAVING   => null
        ];

        // get all preview values (if they exist inside the product options).
        foreach ($cartOptions as $cartOption) {
            if (!isset($cartOption['option_id'])) {
                continue;
            }

            $optionId = $cartOption['option_id'];

            foreach ($productOptions as $key => $value) {
                if ($optionId == reset($value)) {
                    if (array_key_exists($key, $previewArray)) {
                        $previewArray[$key] = ['label' => $cartOption['value']];
                    }
                    break 1;
                }
            }
        }

        $this->populatePreview($previewArray, $productOptions);

        return $previewArray;
    }

    /**
     * this function will build out and return the preview array with necessary values for html output.
     * @param $previewArray
     * @param $productOptions
     */
    protected function populatePreview(&$previewArray, $productOptions)
    {
        foreach ($previewArray as $key => $value) {
            if ($value) { // if we do not have a null value
                switch ($key) {
                    case $this::MATERIAL:
                        $optionCollection = $this->optionValueFactory->create();

                        $collection = $optionCollection->addFieldToFilter(
                            'option_id',
                            reset($productOptions['material_color'])
                        );

                        $collection->addTitlesToResult($this->storeManager->getStore()->getId());

                        $collection = $this->swatchAddition->addSwatchesToResult(
                            $collection,
                            $this->storeManager->getStore()->getId()
                        );

                        $attributes = $collection->getItems();
                        $url        = null;
                        $color      = null;
                        foreach ($attributes as $attribute) {
                            if ($attribute['title'] == reset($value)) { // we have a match.
                                if ($attribute->getImage()) { // get swatch image url.
                                    $url = $this->swatchStorage->getMediaUrl($attribute->getImage());
                                } else { // get css color or hex value.
                                    $color = $attribute['swatch_color'];
                                }
                                break 1;
                            }
                        }

                        $previewArray[$key]['url']   = $url;
                        $previewArray[$key]['color'] = $color;
                        break;
                    case $this::LEFT_CHARM:
                    case $this::RIGHT_CHARM:
                        if (reset($value)) {
                            $charm                     = $this->buildSearchCriteria('title', reset($value), 'charm');
                            $charm                     = $charm->getItems();
                            $charm                     = reset($charm);
                            $fullUrl                   = $this->charmStorage->getMediaUrl($charm->geticon());
                            $previewArray[$key]['url'] = $fullUrl;
                        }
                        break;
                    case $this::LEFT_ICON:
                    case $this::TOP_ICON:
                    case $this::RIGHT_ICON:
                        if (reset($value)) {
                            $icon                      = $this->buildSearchCriteria('title', reset($value), 'icon');
                            $icon                      = $icon->getItems();
                            $icon                      = reset($icon);
                            $fullUrl                   = $this->iconStorage->getMediaUrl($icon->geticon());
                            $previewArray[$key]['url'] = $fullUrl;
                        }
                        break;
                    case $this::ENGRAVING:
                        $engraving = [];

                        foreach (explode(PHP_EOL, reset($value)) as $item) {
                            array_push($engraving, $item);
                        }

                        // line removal for non-name products since they are saved with a trailing /n.
                        if (end($engraving) == "") {
                            array_pop($engraving);
                        }

                        $previewArray[$key]['lines'] = $engraving;
                        break;
                }
            }
        }
    }

    /**
     * this function will build out a filter, filter group, and search-criteria interface
     * to build both charm and icon repository lists.
     * @param $field
     * @param $value
     * @param $flag
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    protected function buildSearchCriteria($field, $value, $flag)
    {
        $filter      = $this->filterBuilder->setField($field)->setValue($value)->create();
        $filterGroup = $this->filterGroupBuilder->addFilter($filter)->create();

        $this->searchCriteriaInterface->setFilterGroups([$filterGroup]);

        if ($flag == 'charm') { // charm code.
            return $this->charmRepo->getList($this->searchCriteriaInterface);
        } else { // icon code.
            return $this->iconRepo->getList($this->searchCriteriaInterface);
        }
    }

    /**
     * this will build out and return the html for bracelet preview.
     * @param $preview
     * @return string
     */
    protected function getHtml($preview)
    {
        $fontClass = $this->engraving->getFontClass($this->cartProd);

        // start main container.
        $html = '<div class="preview-container">';

        // start left charm.
        if (isset($preview['charm_left']) && count($preview['charm_left']) > 1) {
            $url = isset($preview['charm_left']['url']) ? 'url(' . $preview['charm_left']['url'] . ') 50% 50% / contain no-repeat scroll padding-box border-box rgba(0, 0, 0, 0)' : '';
            $html .= '<div class="charm-left charm" title="' . $preview['charm_left']['label'] . '" style="background: ' . $url . '">';
            $html .= '</div>';
            $html .= '<div class="left-divider divider"></div>';
        }
        // end left charm.

        // start main preview: contains left-top-right icons as well as the engraving.
        $style = null;
        if (isset($preview['material_color']['url'])) { // swatch images.
            $style = 'url(' . $preview['material_color']['url'] . ')';
        } elseif (isset($preview['material_color']['color'])) { // swatch color and hex.
            $style = $preview['material_color']['color'];
        }
        $html .= '<div class="preview" style="background: ' . $style . '">';

        // start left icon.
        if (isset($preview['icon_left']) && count($preview['icon_left']) > 1) {
            $url = isset($preview['icon_left']['url']) ? 'url(' . $preview['icon_left']['url'] . ') 50% 50% / contain no-repeat scroll padding-box border-box rgba(0, 0, 0, 0)' : '';
            $html .= '<div class="icon-left icon" title="' . $preview['icon_left']['label'] . '" style="background: ' . $url . '">';
            $html .= '</div>';
        }
        // end left icon.

        //start middle container: contains top icon and engraving.
        $html .= '<div class="middle-preview-container">';

        // start top icon.
        if (isset($preview['icon_top']) && count($preview['icon_top']) > 1) {
            $topIcon = 1;
            $url     = isset($preview['icon_top']['url']) ? 'url(' . $preview['icon_top']['url'] . ') 50% 50% / contain no-repeat scroll padding-box border-box rgba(0, 0, 0, 0)' : '';
            $html .= '<div class="icon-top icon" title="' . $preview['icon_top']['label'] . '" style="background: ' . $url . '">';
            $html .= '</div>';
        }
        // end top icon.

        // start engraving.
        if (isset($preview['engraving'])) {
            $style = isset($topIcon) ? 'style="margin-top:1px;"' : '';
            $html .= '<div class="text-wrapper ' . $fontClass . '" ' . $style . '>';
            foreach ($preview['engraving']['lines'] as $line) {
                if (strlen(trim($line)) !== 0) { // ignore lines that are just carriage returns
                    $html .= '<p class="engraving-text">' . $line . '</p>';
                }
            }
            $html .= '</div>';
        }
        // end engraving.

        $html .= '</div>';
        // end middle container.

        // start right icon.
        if (isset($preview['icon_right']) && count($preview['icon_right']) > 1) {
            $url = isset($preview['icon_right']['url']) ? 'url(' . $preview['icon_right']['url'] . ') 50% 50% / contain no-repeat scroll padding-box border-box rgba(0, 0, 0, 0)' : '';
            $html .= '<div class="icon-right icon" title="' . $preview['icon_right']['label'] . '" style="background: ' . $url . '">';
            $html .= '</div>';
        }
        // end right icon.

        $html .= '</div>';
        // end preview.

        // start right charm.
        if (isset($preview['charm_right']) && count($preview['charm_right']) > 1) {
            $html .= '<div class="right-divider divider"></div>';
            $url = isset($preview['charm_right']['url']) ? 'url(' . $preview['charm_right']['url'] . ') 50% 50% / contain no-repeat scroll padding-box border-box rgba(0, 0, 0, 0)' : '';
            $html .= '<div class="charm_right charm" title="' . $preview['charm_right']['label'] . '" style="background: ' . $url . '">';
            $html .= '</div>';
        }
        // end right charm.

        $html .= '</div>';
        // end main container.

        return $html;
    }
}
