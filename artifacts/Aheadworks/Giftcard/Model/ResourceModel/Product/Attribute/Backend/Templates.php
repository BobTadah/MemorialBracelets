<?php
namespace Aheadworks\Giftcard\Model\ResourceModel\Product\Attribute\Backend;

class Templates extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_giftcard_product_entity_templates', 'value_id');
    }

    /**
     * @param $entityId
     * @return array
     */
    public function loadTemplatesData($entityId)
    {
        $connection = $this->getConnection();
        $columns = [
            'value_id' => $this->getIdFieldName(),
            'template' => 'value',
            'image' => 'image',
            'store_id' => 'store_id'
        ];
        $select = $connection->select()
            ->from($this->getMainTable(), $columns)
            ->where('entity_id = ?', $entityId)
        ;
        return $connection->fetchAll($select);
    }

    /**
     * @param \Magento\Framework\DataObject $templateObject
     * @return int
     */
    public function deleteTemplatesData(\Magento\Framework\DataObject $templateObject)
    {
        $connection = $this->getConnection();
        $conditions = [
            $connection->quoteInto('entity_id = ?', $templateObject->getEntityId()),
            $connection->quoteInto('value = ?', $templateObject->getValue()),
            $connection->quoteInto('store_id = ?', $templateObject->getStoreId())
        ];
        $where = implode(' AND ', $conditions);
        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * @param \Magento\Framework\DataObject $templateObject
     * @return $this
     */
    public function saveTemplatesData(\Magento\Framework\DataObject $templateObject)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($templateObject, $this->getMainTable());

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
