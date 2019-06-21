<?php
namespace Aheadworks\Giftcard\Model\Source\Entity\Attribute;

/**
 * Class GiftcardEmailTemplate
 * @package Aheadworks\Giftcard\Model\Source\Entity\Attribute
 */
class GiftcardEmailTemplate extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    protected $emailTemplates;

    /**
     * @param \Magento\Config\Model\Config\Source\Email\Template $emailTemplates
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Email\Template $emailTemplates
    ) {
        $this->emailTemplates = $emailTemplates;
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->emailTemplates->setPath('aw_giftcard_email_template')->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir direction
     * @return $this
     */
    public function addValueSortToCollection($collection, $dir = \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeId = $this->getAttribute()->getId();
        $attributeTable = $this->getAttribute()->getBackend()->getTable();

        $valueTable1 = $attributeCode . '_t1';
        $valueTable2 = $attributeCode . '_t2';
        $collection->getSelect()
            ->joinLeft(
                [$valueTable1 => $attributeTable],
                "e.entity_id={$valueTable1}.entity_id" .
                " AND {$valueTable1}.attribute_id='{$attributeId}'" .
                " AND {$valueTable1}.store_id='0'",
                []
            )
            ->joinLeft(
                [$valueTable2 => $attributeTable],
                "e.entity_id={$valueTable2}.entity_id" .
                " AND {$valueTable2}.attribute_id='{$attributeId}'" .
                " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                []
            )
        ;
        $valueExpr = $collection->getConnection()
            ->getCheckSql(
                $valueTable2 . '.value_id > 0',
                $valueTable2 . '.value',
                $valueTable1 . '.value'
            )
        ;
        $collection->getSelect()->order($valueExpr . ' ' . $dir);
        return $this;
    }
}
