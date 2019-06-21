<?php
namespace Aheadworks\Giftcard\Model\ResourceModel\Product\Attribute\Backend;

class Amounts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_giftcard_product_entity_amounts', 'value_id');
    }

    /**
     * @param $entityId
     * @return array
     */
    public function loadAmountData($entityId)
    {
        $connection = $this->getConnection();
        $columns = [
            'value_id' => $this->getIdFieldName(),
            'price' => 'value',
            'website_id' => 'website_id',
        ];
        $select = $connection->select()->from($this->getMainTable(), $columns)->where('entity_id=?', $entityId);
        return $connection->fetchAll($select);
    }

    /**
     * @param \Magento\Framework\DataObject $amountObject
     * @return int
     */
    public function deleteAmountData(\Magento\Framework\DataObject $amountObject)
    {
        $connection = $this->getConnection();
        $conditions = [
            $connection->quoteInto('entity_id = ?', $amountObject->getEntityId()),
            $connection->quoteInto('value = ?', $amountObject->getPrice())
        ];
        if ($amountObject->getWebsiteId()) {
            $conditions[] = $connection->quoteInto('website_id = ?', $amountObject->getWebsiteId());
        }
        $where = implode(' AND ', $conditions);
        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * @param \Magento\Framework\DataObject $amountObject
     * @return $this
     */
    public function saveAmountData(\Magento\Framework\DataObject $amountObject)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($amountObject, $this->getMainTable());

        if (!empty($data[$this->getIdFieldName()])) {
            $where = $connection->quoteInto($this->getIdFieldName() . ' = ?', $data[$this->getIdFieldName()]);
            unset($data[$this->getIdFieldName()]);
            $connection->update($this->getMainTable(), $data, $where);
        } else {
            $connection->insert($this->getMainTable(), $data);
        }
        return $this;
    }
}
