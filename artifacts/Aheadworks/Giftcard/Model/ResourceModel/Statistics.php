<?php
namespace Aheadworks\Giftcard\Model\ResourceModel;

/**
 * Statistics resource model
 */
class Statistics extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var int|null
     */
    protected $_productId = null;

    /**
     * @var int|null
     */
    protected $_storeId = null;

    protected function _construct()
    {
        $this->_init('aw_giftcard_statistics', 'id');
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;
        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function attachStatistics($collection)
    {
        $collection->getSelect()
            ->joinLeft(
                ['giftcard_statistics_table' => $this->getTable('aw_giftcard_statistics')],
                'e.entity_id = giftcard_statistics_table.product_id',
                [
                    'purchased_qty' => 'giftcard_statistics_table.purchased_qty',
                    'purchased_amount' => 'giftcard_statistics_table.purchased_amount',
                    'used_qty' => 'giftcard_statistics_table.used_qty',
                    'used_amount' => 'giftcard_statistics_table.used_amount'
                ]
            )
        ;
    }

    /**
     * Check whether exists statistics with given $productId and $storeId
     *
     * @param int $productId
     * @param int $storeId
     * @return bool
     */
    public function existsStatistics($productId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select
            ->from($this->getMainTable(), 'id')
            ->where('product_id = ?', $productId)
            ->where('store_id = ?', $storeId)
        ;

        if ($connection->fetchOne($select) === false) {
            return false;
        }
        return true;
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($this->_productId) {
            $select->where('product_id = ?', $this->_productId);
        }
        if ($this->_storeId) {
            $select->where('store_id = ?', $this->_storeId);
        }
        $this->_productId = null;
        $this->_storeId = null;
        return $select;
    }
}