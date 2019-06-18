<?php

namespace MemorialBracelets\NameProduct\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\Eav\Model\Config;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Msrp\Helper\Data as MsrpData;
use Magento\Store\Model\StoreManagerInterface;
use MemorialBracelets\NameProduct\Model\ResourceModel\Product\Link;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Name extends AbstractType
{
    const TYPE_CODE = 'name';

    const WTC_EVENTS = [
        'THE WTC 1993',
        'THE WTC 2001'
    ];

    protected $keyAssociatedProducts = '_cache_instance_nameassociated_products';

    protected $keyAssociatedProductIds = '_cache_instance_nameassociated_products_ids';

    protected $keyStatusFilters = '_cache_instance_status_filters';

    protected $isComposite = true;

    protected $canConfigure = true;

    protected $catalogProductStatus;

    protected $storeManager;

    /** @var Link */
    protected $productLinks;

    /** @var AppState */
    protected $appState;

    /** @var MsrpData */
    protected $msrpData;

    /** @var ProductFactory */
    protected $productFactory;

    /**
     * Name constructor.
     *
     * @param Option                     $catalogProductOption
     * @param Config                     $eavConfig
     * @param Type                       $catalogProductType
     * @param ManagerInterface           $eventManager
     * @param Database                   $fileStorageDb
     * @param Filesystem                 $filesystem
     * @param Registry                   $coreRegistry
     * @param LoggerInterface            $logger
     * @param ProductRepositoryInterface $productRepository
     * @param Link                       $catalogProductLink
     * @param StoreManagerInterface      $storeManager
     * @param Status                     $catalogProductStatus
     * @param AppState                   $appState
     * @param MsrpData                   $msrpData
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Option $catalogProductOption,
        Config $eavConfig,
        Type $catalogProductType,
        ManagerInterface $eventManager,
        Database $fileStorageDb,
        Filesystem $filesystem,
        Registry $coreRegistry,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        Link $catalogProductLink,
        StoreManagerInterface $storeManager,
        Status $catalogProductStatus,
        AppState $appState,
        MsrpData $msrpData,
        ProductFactory $productFactory
    ) {
        $this->productLinks = $catalogProductLink;
        $this->storeManager = $storeManager;
        $this->catalogProductStatus = $catalogProductStatus;
        $this->appState = $appState;
        $this->msrpData = $msrpData;
        $this->productFactory = $productFactory;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
    }

    /**
     * Return relation information about used products
     *
     * @return DataObject
     */
    public function getRelationInfo()
    {
        $info = new DataObject();
        $info->setData('table', 'catalog_product_link')
            ->setData('parent_field_name', 'product_id')
            ->setData('child_field_name', 'linked_product_id')
            ->setData('where', 'link_type_id='.Link::LINK_TYPE_NAME);

        return $info;
    }

    /**
     * Retrieve required children ids
     * Return grouped array, ex [{@see Link::LINK_TYPE_NAME} => [ids]]
     *
     * @param int  $parentId
     * @param bool $required
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return $this->productLinks->getChildrenIds($parentId, Link::LINK_TYPE_NAME);
    }

    /** @inheritdoc */
    public function getParentIdsByChild($childId)
    {
        return $this->productLinks->getParentIdsByChild($childId, Link::LINK_TYPE_NAME);
    }

    /**
     * @param Product $product
     * @return array|mixed
     */
    public function getConfiguredProducts($product)
    {
        if (!$product->hasData($this->keyAssociatedProducts)) {
            $associatedProducts = [];

            $this->setSaleableStatus($product);

            $collection = $this->getAssociatedProductCollection($product)
                ->addAttributeToSelect(['name', 'price', 'special_price', 'special_from_date', 'special_to_date', 'has_options', 'producttype'])
                ->setPositionOrder()
                ->addOptionsToResult()
                ->addMediaGalleryData()
                ->addStoreFilter($this->getStoreFilter($product))
                ->addAttributeToFilter('status', ['in' => $this->getStatusFilters($product)])
                ->setOrder('position', Select::SQL_ASC);

            //Need to reorder the Options here, because they aren't brought in through the Select
            $collection = $this->addSortedOptionsToResult($collection);

            foreach ($collection as $item) {
                $associatedProducts[] = $item;
            }

            $product->setData($this->keyAssociatedProducts, $associatedProducts);
        }
        return $product->getData($this->keyAssociatedProducts);
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function flushAssociatedProductsCache($product)
    {
        return $product->unsetData($this->keyAssociatedProducts);
    }

    /**
     * @param int     $status
     * @param Product $product
     * @return $this
     */
    public function addStatusFilter($status, $product)
    {
        $statusFilters = $product->getData($this->keyStatusFilters);
        if (!is_array($statusFilters)) {
            $statusFilters = [];
        }

        $statusFilters[] = $status;
        $product->setData($this->keyStatusFilters, $statusFilters);

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setSaleableStatus($product)
    {
        $product->setData($this->keyStatusFilters, $this->catalogProductStatus->getSaleableStatusIds());
        return $this;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getStatusFilters($product)
    {
        if (!$product->hasData($this->keyStatusFilters)) {
            return [Status::STATUS_ENABLED, Status::STATUS_DISABLED];
        }
        return $product->getData($this->keyStatusFilters);
    }

    /**
     * Retrieve related products identifiers
     *
     * @param Product $product
     * @return array
     */
    public function getAssociatedProductIds($product)
    {
        if (!$product->hasData($this->keyAssociatedProductIds)) {
            $associatedProductIds = [];
            /** @var $item Product */
            foreach ($this->getConfiguredProducts($product) as $item) {
                $associatedProductIds[] = $item->getId();
            }
            $product->setData($this->keyAssociatedProductIds, $associatedProductIds);
        }
        return $product->getData($this->keyAssociatedProductIds);
    }

    /**
     * Retrieve collection of associated products
     *
     * @param Product $product
     * @return Collection
     */
    public function getAssociatedProductCollection($product)
    {
        /** @var Product\Link $links */
        $links = $product->getLinkInstance();
        $links->setLinkTypeId(Link::LINK_TYPE_NAME);

        return $links->getProductCollection()
            ->setFlag('product_children', true)
            ->setIsStrongMode()
            ->setProduct($product);
    }

    /**
     * @param DataObject $buyRequest
     * @param Product    $product
     * @param bool       $isStrictProcessMode
     * @return array|string
     */
    protected function getProductInfo(DataObject $buyRequest, $product, $isStrictProcessMode)
    {
        $productsInfo = $buyRequest->getData('super_group') ?: [];
        $associatedProducts = $this->getConfiguredProducts($product);

        if (!is_array($productsInfo)) {
            return __('Please specify the quantity of product(s).')->render();
        }
        foreach ($associatedProducts as $subProduct) {
            if (!isset($productsInfo[$subProduct->getId()])) {
                $productsInfo[$subProduct->getId()] = 0;
            }
        }

        return $productsInfo;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getNameInfo($product)
    {
        /** @var \MemorialBracelets\NameProduct\Model\Product\Type\Name $type */
        $originalIncidentDate = $product->getData('date');
        if (!$originalIncidentDate == null) {
            $incidentTimestamp = strtotime($originalIncidentDate);
            $incidentDate = strtoupper(date('d M y', $incidentTimestamp));
        } else {
            $incidentDate = '';
        }

        // Special Request products have a special engraving that is treated as the name information engraving

        if ($product->getData('special_engraving')) {
            $engraving = explode("\n", $product->getData('special_engraving'));
            $engraving = array_map(
                function ($line) {
                    return trim($line, "\r\n");
                },
                $engraving
            );
            return implode("\r\n", $engraving);
        }

        // Rank, First name, Middle Initial, Last Name, and Suffix are bundled into product name
        // For simplicity just use that bundled value, but keep ability to use them separately in future.

        $name = $product->getData('name');
        $state = $this->nameInfoAttributeText($product, 'state');
        $country = $this->nameInfoAttributeText($product, 'country');
        $incidentCountry = $product->getData('incident_country');
        $event = $this->nameInfoAttributeText($product, 'event');
        $status = $this->nameInfoAttributeText($product, 'name_status');
        $affiliation = $this->nameInfoAttributeText($product, 'affiliation');
        $wallnumber = $product->getData('wallnumber');

        //Default spacing.
        $sp1            = '  ';
        $spState        = (!empty($state)) ? $sp1 : null;
        $spAffiliation  = (!empty($affiliation)) ? $sp1 : null;
        if (strtoupper($this->nameInfoAttributeText($product, 'name_status')) == 'VOT') {
            //If is one of the WTC we don't want to show the year. So we just set it to be just 'THE WTC'
            if (in_array($event, self::WTC_EVENTS)) {
                $event = 'THE WTC';
            }

            $lines = [
                $name . $spState . $state . $sp1 . $country,
                $event . $sp1 . $incidentDate . $sp1 . $status,
            ];
        } else {
            if (in_array(strtoupper($event), [
                    'VIETNAM WAR', 'VIETNAM WAR PRISONER OF WAR', 'VIETNAM WAR MISSING IN ACTION', 'VIETNAM WAR RETURNEE'
                ])) {
                if ($status != 'KIA') {
                    $wallnumber = $status;
                }

                // Rank FName MInitial LName Suffix
                // Affiliation  Day Month Year  Country
                // WallNumber(or MIA)          StateAbbreviation //(10 spaces in between Wall Number and state)
                // The third line is 10 spaces apart as they represent left and right sides of the actual bracelet
                $lines = [
                    $name,
                    $affiliation .  $sp1 . $incidentDate . $sp1 . $incidentCountry,
                    $wallnumber . $spState . $state,
                ];
            } else {
                // Rank FName MInitial LName Suffix  StateAbbreviation  Affiliation
                // Event  Day Month Year  Status
                $lines = [
                    $name . $spState . $state . $spAffiliation . $affiliation,
                    $event . $sp1 . $incidentDate . $sp1. $status,
                ];
            }
        }
        return implode("\r\n", $lines);
    }

    public function getNameListRow($product)
    {
        /** @var \MemorialBracelets\NameProduct\Model\Product\Type\Name $type */
        $o_date = $product->getData('date');
        if (!$o_date == null) {
            $xx = strtotime($o_date);
            $f_date = date('d M y', $xx);
        } else {
            $f_date = '';
        }

        // Rank, First name, Middle Initial, Last Name, and Suffix are bundled into product name
        // For simplicity just use that bundled value, but keep ability to use them separately in future.
        $specialRequestEvent        = ($product->getCustomAttribute('special_request_event')) ? $product->getCustomAttribute('special_request_event')->getValue() : null;
        $specialRequestAffiliation  = ($product->getCustomAttribute('special_request_affiliation')) ? $product->getCustomAttribute('special_request_affiliation')->getValue() : null;
        $specialRequestStatus       = ($product->getCustomAttribute('special_request_status')) ? $product->getCustomAttribute('special_request_status')->getValue() : null;
        $event          = ($this->nameInfoAttributeText($product, 'event')) ? $this->nameInfoAttributeText($product, 'event') : $specialRequestEvent;
        $affiliation    = ($this->nameInfoAttributeText($product, 'affiliation')) ? $this->nameInfoAttributeText($product, 'affiliation') : $specialRequestAffiliation;
        $status         = ($this->nameInfoAttributeText($product, 'name_status')) ? $this->nameInfoAttributeText($product, 'name_status') : $specialRequestStatus;

        $nameString =
            '<td>' . $event . '</td>
             <td>' . $affiliation .'</td>
             <td>' . $status . '</td>
             <td>' . $product->getData('city')  . '</td>
             <td>' . $this->nameInfoAttributeText($product, 'state') . '</td>
             <td>' . $product->getData('age') . '</td>
             <td>' . $f_date . '</td>';
        return $nameString;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $field
     * @return string
     */
    private function nameInfoAttributeText($product, $field)
    {
        return $product->getData($field) ? $product->getAttributeText($field) : '';
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * @param DataObject $buyRequest
     * @param Product    $product
     * @param string     $processMode
     * @return Phrase|array|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareProduct(DataObject $buyRequest, $product, $processMode)
    {
        $products = [];
        $associatedProductsInfo = [];
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);
        $productsInfo = $this->getProductInfo($buyRequest, $product, $isStrictProcessMode);
        if (is_string($productsInfo)) {
            return $productsInfo;
        }
        $associatedProducts = !$isStrictProcessMode || !empty($productsInfo)
            ? $this->getConfiguredProducts($product)
            : false;

        foreach ($associatedProducts as $subProduct) {
            $qty = $productsInfo[$subProduct->getId()];
            if (!is_numeric($qty) || empty($qty)) {
                continue;
            }

            //$subProduct->prepareCustomOptions();
            /** @var \Magento\Catalog\Model\Product\Configuration\Item\Option $customOption */
/*            foreach ($subProduct->getCustomOptions() as $customOption) {
                $product->addCustomOption(
                    $customOption->getCode(),
                    $customOption->getValue(),
                    $customOption->getProductId()
                );
            }*/

            // Need to pass in information about the name product for Price validation
            $nameBuyRequest = $buyRequest->getData();
            $nameString = $this->getNameInfo($product);
            $nameBuyRequest['name_string'] = $nameString;

            $buyRequest->setData($nameBuyRequest);

            /** @var Product[] $_result */
            $_result = $subProduct->getTypeInstance()->_prepareProduct($buyRequest, $subProduct, $processMode);

            if (is_string($_result)) {
                return $_result;
            } elseif (!isset($_result[0])) {
                return __('Cannot process the item.')->render();
            }

            if ($isStrictProcessMode) {
                $_result[0]->setData('cart_qty', $qty);
                $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);
                $initialBuyRequest = $buyRequest->getData();
                $initialBuyRequest['super_product_config'] = [
                    'product_type' => self::TYPE_CODE,
                    'product_id'   => $product->getId(),
                ];
                $product->addCustomOption('info_buyRequest', serialize($initialBuyRequest));
                $products[] = $_result[0];
            } else {
                $associatedProductsInfo[] = [$subProduct->getId() => $qty];
                $product->addCustomOption('nameassociated_product_'.$subProduct->getId(), $qty);
            }
        }

        if (!$isStrictProcessMode || count($associatedProductsInfo)) {
            $product->addCustomOption('product_type', self::TYPE_CODE, $product);
            $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));

            $products[] = $product;
        }

        if (count($products)) {
            return $products;
        }

        return __('Please specify the quantity.')->render();
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        return [$this->getConfiguredProducts($product)];
    }

    /**
     * Prepare selected qty for name product's options
     *
     * @param  Product                       $product
     * @param  \Magento\Framework\DataObject $buyRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $superGroup = $buyRequest->getData('super_group');
        $superGroup = is_array($superGroup) ? array_filter($superGroup, 'intval') : [];

        $options = ['super_group' => $superGroup];

        return $options;
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Delete data specific for Name product type
     *
     * @param Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteTypeSpecificData(Product $product)
    {
        // This function intentionally does nothing
    }

    public function beforeSave($product)
    {
        //clear cached associated links
        $product->unsetData($this->keyAssociatedProducts);
        parent::beforeSave($product);
    }

    /**
     * @param Product $product
     * @return int
     */
    public function getChildrenMsrp(Product $product)
    {
        $prices = [];
        foreach ($this->getConfiguredProducts($product) as $item) {
            if ($item->getMsrp() !== null) {
                $prices[] = $item->getMsrp();
            }
        }
        return $prices ? min($prices) : 0;
    }

    /** {@inheritdoc} */
    public function hasOptions($product)
    {
        $children = $this->getAssociatedProductCollection($product)->addAttributeToSelect(['has_options']);

        foreach ($children as $child) {
            if ($child->getTypeInstance()->hasOptions($child)) {
                return true;
            }
        }
        return false;
    }

    /** {@inheritdoc} */
    public function hasRequiredOptions($product)
    {
        $children = $this->getAssociatedProductCollection($product);

        foreach ($children as $child) {
            if ($child->getTypeInstance()->hasRequiredOptions($child)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adding product custom options to result collection
     *
     * @return $this
     */
    public function addSortedOptionsToResult($collection)
    {
        foreach ($collection as $product) {
            if ($product->getData('has_options')) {
                $unsortedOptions = $product->getOptions();
                $sortedOptions = [];
                foreach ($unsortedOptions as $option) {
                    $sortOrder = $option->getData('sort_order');
                    $sortedOptions[$sortOrder] = $option;
                }
                ksort($sortedOptions);
                $product->setOptions($sortedOptions);
            }
        }
        return $collection;
    }

    /**
     * We implement this method to process in FULL mode.
     * This is necessary for Name Products to be correctly added to the Wish List.
     *
     * @param DataObject $buyRequest
     * @param Product $product
     * @param string $processMode
     * @return array|string
     */
    public function processConfiguration(
        DataObject $buyRequest,
        $product,
        $processMode = self::PROCESS_MODE_FULL
    ) {
        return parent::processConfiguration($buyRequest, $product, $processMode);
    }
}
