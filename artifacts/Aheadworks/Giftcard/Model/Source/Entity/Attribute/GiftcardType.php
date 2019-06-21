<?php
namespace Aheadworks\Giftcard\Model\Source\Entity\Attribute;

use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Class GiftcardType
 *
 * @package Aheadworks\Giftcard\Model\Source\Entity\Attribute
 */
class GiftcardType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Option values
     */
    const VALUE_VIRTUAL = 1;
    const VALUE_PHYSICAL = 2;
    const VALUE_COMBINED = 3;

    /**
     * Option labels
     */
    const LABEL_VIRTUAL = 'Virtual';
    const LABEL_PHYSICAL = 'Physical';
    const LABEL_COMBINED = 'Combined';

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory
     */
    protected $_eavAttrEntity;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->_eavAttrEntity = $eavAttrEntity;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __(self::LABEL_VIRTUAL), 'value' => self::VALUE_VIRTUAL],
                ['label' => __(self::LABEL_PHYSICAL), 'value' => self::VALUE_PHYSICAL],
                ['label' => __(self::LABEL_COMBINED), 'value' => self::VALUE_COMBINED],
            ];
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
        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();

        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeId = $this->getAttribute()->getId();
        $attributeTable = $this->getAttribute()->getBackend()->getTable();

        $tableName = $attributeCode . '_t';
        $collection->getSelect()
            ->joinLeft(
                [$tableName => $attributeTable],
                "e.{$linkField}={$tableName}.{$linkField}" .
                " AND {$tableName}.attribute_id='{$attributeId}'" .
                " AND {$tableName}.store_id='0'",
                []
            )
        ;
        $collection->getSelect()->order($tableName . '.value ' . $dir);
        return $this;
    }
}
