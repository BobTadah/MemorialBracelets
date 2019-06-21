<?php
namespace Aheadworks\Giftcard\Model;

class Statistics extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var StatisticsFactory
     */
    protected $_statisticsFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param StatisticsFactory $statisticsFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        StatisticsFactory $statisticsFactory
    ) {
        parent::__construct($context, $registry);
        $this->_statisticsFactory = $statisticsFactory;
    }

    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\ResourceModel\Statistics');
    }

    public function loadByProductAndStoreId($productId, $storeId)
    {
        $this->getResource()->setStoreId($storeId);
        return $this->load($productId, 'product_id');
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function attachStatistics($collection)
    {
        $this->getResource()->attachStatistics($collection);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function createStatistics($product)
    {
        $this->_createNewRowIfNotExists($product->getId(), $product->getStoreId());
    }

    /**
     * Update statistics data
     *
     * @param int $productId
     * @param int $storeId
     * @param array $data
     */
    public function updateStatistics($productId, $storeId, array $data)
    {
        /** @var Statistics $statistics */
        if (!$statistics = $this->_createNewRowIfNotExists($productId, $storeId)) {
            // todo: store view scope
            $statistics = $this->_statisticsFactory->create()->loadByProductAndStoreId($productId, 0);
        }
        foreach ($data as $key => $value) {
            $statistics->setData($key, $statistics->getData($key) + $value);
        }
        $statistics->save();
    }

    protected function _createNewRowIfNotExists($productId, $storeId)
    {
        $statistics = false;
        // todo: store view scope
        if (!$this->getResource()->existsStatistics($productId, 0)) {
            $statistics = $this->_statisticsFactory->create()
                ->setData(
                    [
                        'product_id' => $productId,
                        // todo: store view scope
                        //'store_id' => $storeId
                        'store_id' => 0
                    ]
                )
                ->save()
            ;
        }
        return $statistics;
    }
}