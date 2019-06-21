<?php
namespace Aheadworks\Giftcard\Ui\DataProvider\Giftcard;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    /**
     * @var array
     */
    protected $statisticsFields = [
        'purchased_qty',
        'purchased_amount',
        'used_qty',
        'used_amount'
    ];

    /**
     * @var string
     */
    protected $templateField = 'aw_gc_email_template';

    /**
     * @var array
     */
    protected $statisticsOrders = [];

    /**
     * @var array
     */
    protected $statisticsFilters = [];

    /**
     * @var null|\Magento\Framework\Api\Filter
     */
    protected $templateFilter = null;

    /**
     * @var \Aheadworks\Giftcard\Model\ResourceModel\Statistics
     */
    protected $statistics;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Aheadworks\Giftcard\Model\ResourceModel\Statistics $statistics
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Aheadworks\Giftcard\Model\ResourceModel\Statistics $statistics,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
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
        $this->collection->addFieldToFilter('type_id', ['eq' => TypeGiftCard::TYPE_CODE]);
        $this->statistics = $statistics;
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (in_array($filter->getField(), $this->statisticsFields)) {
            $this->statisticsFilters[] = $filter;
        } else if ($filter->getField() == $this->templateField) {
            $this->templateFilter = $filter;
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * @param string $field
     * @param string $direction
     */
    public function addOrder($field, $direction)
    {
        if (in_array($field, $this->statisticsFields)) {
            $this->statisticsOrders[$field] = $direction;
        } else {
            parent::addOrder($field, $direction);
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->attachStatistics();
            $this->applyTemplateFilter();
            $this->getCollection()->load();

            /** @var $product \Magento\Catalog\Model\Product */
            foreach ($this->getCollection() as $product) {
                $product->getResource()->load($product, $product->getId());
            }
        }
        return parent::getData();
    }

    /**
     * Attach statistics data to collection
     */
    protected function attachStatistics()
    {
        $collection = $this->getCollection();
        $this->statistics->attachStatistics($collection);
        foreach ($this->statisticsOrders as $field => $direction) {
            $collection->getSelect()->order($field . ' ' . $direction);
        }
        /** @var $filter \Magento\Framework\Api\Filter */
        foreach ($this->statisticsFilters as $filter) {
            $collection->getSelect()
                ->where(
                    $collection->getConnection()->prepareSqlCondition(
                        'giftcard_statistics_table.' . $filter->getField(),
                        [$filter->getConditionType() => $filter->getValue()]
                    )
                )
            ;
        }
    }

    /**
     * Apply filtering by email template
     */
    protected function applyTemplateFilter()
    {
        if ($filter = $this->templateFilter) {
            $collection = $this->getCollection();
            $collection->getSelect()
                ->joinLeft(
                    ['giftcard_templates_table' => $collection->getTable('aw_giftcard_product_entity_templates')],
                    'e.entity_id = giftcard_templates_table.entity_id',
                    []
                )
                ->where($collection->getConnection()->prepareSqlCondition(
                    'giftcard_templates_table.value',
                    [$filter->getConditionType() => $filter->getValue()]
                ))
            ;
        }
    }
}
