<?php

namespace MemorialBracelets\NameCategory\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use MemorialBracelets\NameCategory\Controller\Index\NameCategory;

/**
 * Product list
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProduct extends AbstractProduct implements IdentityInterface
{
    /** @var string $defaultToolbarBlock */
    protected $defaultToolbarBlock = 'MemorialBracelets\NameCategory\Block\Product\ProductList\Toolbar';

    /** @var AbstractCollection $productCollection */
    protected $productCollection;

    /** @var Layer $catalogLayer*/
    protected $catalogLayer;

    /** @var PostHelper $postDataHelper*/
    protected $postDataHelper;

    /** @var UrlHelper $urlHelper*/
    protected $urlHelper;

    /** @var CategoryRepositoryInterface $categoryRepository */
    protected $categoryRepository;

    /** @var AttributeRepositoryInterface $attributeRepo */
    protected $attributeRepo;
    /**
     * @var NameCategory
     */
    protected $nameCategory;

    /**
     * ListProduct constructor.
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UrlHelper $urlHelper
     * @param AttributeRepositoryInterface $attributeRepo
     * @param NameCategory $nameCategory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        UrlHelper $urlHelper,
        AttributeRepositoryInterface $attributeRepo,
        NameCategory $nameCategory,
        array $data = []
    ) {
        $this->catalogLayer      = $layerResolver->get();
        $this->postDataHelper    = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper          = $urlHelper;
        $this->attributeRepo      = $attributeRepo;
        parent::__construct($context, $data);
        $this->nameCategory = $nameCategory;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    protected function _getProductCollection()
    {
        if ($this->productCollection === null) {
            $layer = $this->getLayer();

            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if ($this->_coreRegistry->registry('product')) {
                // get collection of categories this product is associated with
                $categories = $this->_coreRegistry->registry('product')->getCategoryCollection()->setPage(1, 1)->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $category = $this->categoryRepository->get($this->getCategoryId());
                } catch (NoSuchEntityException $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->productCollection = $layer->getProductCollection()->addAttributeToSelect(
                [
                    'event',
                    'affiliation',
                    'date',
                    'name_status',
                    'city',
                    'state',
                    'age'
                ]
            );


            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        return $this->productCollection;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->catalogLayer;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    public function beforeToHtml()
    {
        $this->_beforeToHtml();
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getProductCollection()]
        );

        $this->_getProductCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();

        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }

        $block = $this->getLayout()->createBlock($this->defaultToolbarBlock, uniqid(microtime()));

        return $block;
    }

    /**
     * Retrieve additional blocks html
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * Retrieve list toolbar HTML
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->productCollection = $collection;

        return $this;
    }

    /**
     * @param array|string|integer|\Magento\Framework\App\Config\Element $code
     * @return $this
     */
    public function addAttribute($code)
    {
        $this->_getProductCollection()->addAttributeToSelect($code);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceBlockTemplate()
    {
        return $this->_getData('price_block_template');
    }

    /**
     * Retrieve Catalog Config object
     * @return \Magento\Catalog\Model\Config
     */
    protected function _getConfig()
    {
        return $this->_catalogConfig;
    }

    /**
     * Prepare Sort By fields from Category Data
     * @param Category $category
     * @return $this
     */
    public function prepareSortableFieldsByCategory(Category $category)
    {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($category->getAvailableSortByOptions());
        }
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            $categorySortBy = $this->getDefaultSortBy() ?: $category->getDefaultSortBy();
            if ($categorySortBy) {
                if (!$availableOrders) {
                    $availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
                }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setSortBy($categorySortBy);
                }
            }
        }

        return $this;
    }

    /**
     * Return identifiers for produced content
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->_getProductCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }

        $category = $this->getLayer()->getCurrentCategory();
        if ($category) {
            $identities[] = Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $category->getId();
        }

        return $identities;
    }

    /**
     * Get post parameters
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);

        return [
            'action' => $url,
            'data'   => [
                'product'                               => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container'     => true,
                    'display_minimal_price' => true,
                    'zone'                  => Render::ZONE_ITEM_LIST,
                    'list_category_page'    => true
                ]
            );
        }

        return $price;
    }

    /**
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default');
    }

    /**
     * Return URL for ajax requests
     * @return string
     */
    public function getAjaxUrl()
    {
        $result = $this->getUrl() . "namecategory/index/namecategory/";

        return $result;
    }

    /**
     * this will attempt to get the attribute admin label values.
     * @return array
     */
    public function getAttributeLabels()
    {
        $attributeCodeArray = [
            'name'        => '',
            'event'       => '',
            'affiliation' => '',
            'name_status' => '',
            'city'        => 'Home Town', // we do not want the label for this attribute
            'state'       => '',
            'age'         => '',
            'date'        => 'Date' // we do not want the label for this attribute
        ];

        foreach ($attributeCodeArray as $key => $code) {
            if (!$code) { // we have no value for the attribute
                $attribute = $this->attributeRepo->get(Product::ENTITY, $key);

                if ($attribute) {
                    $attributeCodeArray[$key] = $attribute->getDefaultFrontendLabel();
                }
            }
        }

        return$attributeCodeArray;
    }

    /**
     * Get current category
     *
     * @return int
     */
    public function getCategoryId()
    {
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->_coreRegistry->registry('current_category');
        $catId = ($category) ? $category->getId() : null;
        return $catId;
    }

    /**
     * Calls to same method that is used when click Get New Name button.
     */
    public function getRandomNameProduct()
    {
        return $this->nameCategory->getNameProducts($this->getCategoryId());
    }
}
