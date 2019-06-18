<?php

namespace MemorialBracelets\ExtensibleCustomOption\Model\ResourceModel\Product\Option;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
{
    /**
     * @return JoinProcessorInterface
     */
    private function getJoinProcessor()
    {
        if (null === $this->joinProcessor) {
            $this->joinProcessor = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface');
        }
        return $this->joinProcessor;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param bool $requiredOnly
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]
     */
    public function getProductOptions($productId, $storeId, $requiredOnly = false)
    {
        // MODIFICATION IS HERE: Moved functionality to getOptionsCollection
        $collection = $this->getOptionsCollection($productId, $storeId);

        if ($requiredOnly) {
            $collection->addRequiredFilter();
        }
        $collection->addValuesToResult($storeId);
        $this->getJoinProcessor()->process($collection);
        return $collection->getItems();
    }

    public function getOptionsCollection($productId, $storeId)
    {
        return $this->addFieldToFilter(
            'cpe.entity_id',
            $productId
        )->addTitleToResult(
            $storeId
        )->addPriceToResult(
            $storeId
        )->setOrder(
            'sort_order',
            'asc'
        )->setOrder(
            'title',
            'asc'
        );
    }

    public function addValuesToResult($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $optionIds = [];
        foreach ($this as $option) {
            $optionIds[] = $option->getId();
        }
        if (!empty($optionIds)) {
            // MODIFICATION IS HERE, We separated functionality into getValuesCollection
            $values = $this->getValuesCollection($storeId, $optionIds);

            foreach ($values as $value) {
                $optionId = $value->getOptionId();
                if ($this->getItemById($optionId)) {
                    $this->getItemById($optionId)->addValue($value);
                    $value->setOption($this->getItemById($optionId));
                }
            }
        }

        return $this;
    }

    /**
     * @param $storeId
     * @param $optionIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    public function getValuesCollection($storeId, $optionIds)
    {
        $values = $this->_optionValueCollectionFactory->create();
        $values->addTitleToResult(
            $storeId
        )->addPriceToResult(
            $storeId
        )->addOptionToFilter(
            $optionIds
        )->setOrder(
            'sort_order',
            self::SORT_ORDER_ASC
        )->setOrder(
            'title',
            self::SORT_ORDER_ASC
        );

        return $values;
    }
}
