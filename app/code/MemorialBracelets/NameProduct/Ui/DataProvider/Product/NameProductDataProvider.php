<?php

namespace MemorialBracelets\NameProduct\Ui\DataProvider\Product;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class NameProductDataProvider extends ProductDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * Construct
     *
     * @param string                           $name
     * @param string                           $primaryFieldName
     * @param string                           $requestFieldName
     * @param CollectionFactory                $collectionFactory
     * @param RequestInterface                 $request
     * @param StoreRepositoryInterface         $storeRepository
     * @param ConfigInterface                  $config
     * @param AddFieldToCollectionInterface[]  $addFieldStrategies
     * @param AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array                            $meta
     * @param array                            $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ConfigInterface $config,
        StoreRepositoryInterface $storeRepository,
        array $meta = [],
        array $data = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->config = $config;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->addAttributeToFilter(
                'type_id',
                $this->config->getComposableTypes()
            );
            if ($storeId = $this->request->getParam('current_store_id')) {
                /** @var StoreInterface $store */
                $store = $this->storeRepository->getById($storeId);
                $this->getCollection()->setStore($store);
            }
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items'        => array_values($items),
        ];
    }
}
