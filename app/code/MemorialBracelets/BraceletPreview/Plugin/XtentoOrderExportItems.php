<?php

namespace MemorialBracelets\BraceletPreview\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Xtento\OrderExport\Model\Export\Data\Shared\Items;

class XtentoOrderExportItems
{
    const ARRAY_KEY = 'bracelet_piece';

    protected $productCollectionFactory;
    protected $searchBuilderFactory;

    public function __construct(CollectionFactory $productCollection)
    {
        $this->productCollectionFactory = $productCollection;
    }

    public function aroundGetExportData(Items $subject, callable $proceed, $entityType, $collectionItem)
    {
        $returnArray = $proceed($entityType, $collectionItem);
        if (!isset($returnArray['items'])) {
            return $returnArray;
        }

        $object = $collectionItem->getObject();
        $items = $object->getAllItems();

        $nameProductIds = $this->collectNameProductIds($items);
        $optionData = $this->collectOptionData($items);
        $nameProducts = $this->getNameProducts($nameProductIds);

        unset($item);

        foreach ($returnArray['items'] as &$item) {
            $productOptionsData = isset($item['product_options_data']) ? $item['product_options_data'] : [];

            if ($this->isNameEngraving($productOptionsData)) {
                $nameProduct = $nameProducts[$productOptionsData['super_product_config']['product_id']];
                $nameProduct->getAttributes();
                $data = $nameProduct->getData();
                foreach ($data as $key => $val) {
                    if ($nameProduct->getResource()->getAttribute($key) &&
                        $nameProduct->getResource()->getAttribute($key)->getSource() &&
                        $nameProduct->getAttributeText($key)
                    ) {
                        $val = $nameProduct->getAttributeText($key);
                    }
                    // Convert Phrases to Text
                    if ($val instanceof Phrase) {
                        $val = $val->getText();
                    }

                    // Remove Objects from Data
                    if (!is_string($val)) {
                        unset($data[$key]);
                    } else {
                        $data[$key] = $val;
                    }
                }
                $item['product_attributes']['name_product'] = $data;
            }

            if (!isset($item['custom_options']) || empty($item['custom_options'])) {
                continue;
            }

            foreach ($item['custom_options'] as &$customOption) {
                if (isset($customOption['option_id'])) {
                    $optionId = $customOption['option_id'];
                    if (!isset($optionData[$optionId]) || !isset($optionData[$optionId][static::ARRAY_KEY])) {
                        continue;
                    }
                    $data = $optionData[$optionId];
                } else {
                    continue;
                }

                $customOption[static::ARRAY_KEY] = $data[static::ARRAY_KEY];
                if ($data[static::ARRAY_KEY] == 'engraving') {
                    $customOption['value'] = trim($customOption['value']);
                }
            }
        }

        return $returnArray;
    }

    /**
     * Return an array of Name Product IDs from an array of Order Items
     *
     * @param DataObject[] $items
     * @return int[]
     */
    protected function collectNameProductIds($items)
    {
        return array_reduce(
            $items,
            function ($result, DataObject $item) {
                $options = $item->getData('product_options');

                if (!$this->isNameEngraving($options)) {
                    return $result;
                }

                $result[] = intval($options['super_product_config']['product_id'], 10);
                return $result;
            },
            []
        );
    }

    /**
     * Return an array of option data indexed by Option ID from an array of Order Items
     *
     * @param DataObject[] $items
     * @return array
     */
    protected function collectOptionData($items)
    {
        return array_reduce(
            $items,
            function ($result, DataObject $item) {
                $options = $item->getData('product_options');

                // Disqualifiers
                if (!isset($options['options'])) {
                    return $result;
                }

                foreach ($options['options'] as $customOption) {
                    $result[$customOption['option_id']] = $customOption;
                }
                return $result;
            },
            []
        );
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function isNameEngraving($data)
    {
        // Disqualifiers
        switch (true) {
            case (!isset($data['super_product_config'])):
            case (!isset($data['super_product_config']['product_type'])):
            case ($data['super_product_config']['product_type'] != 'name'):
                return false;
        }
        return true;
    }

    /**
     * @param $ids
     * @return Product[] indexed by Product ID
     */
    protected function getNameProducts($ids)
    {
        $collection = $this->productCollectionFactory->create();
        $collection
            ->addAttributeToSelect('*')
            ->addIdFilter($ids);

        /** @var Product[] $items */
        $items = $collection->getItems();
        $result = [];

        foreach ($items as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }
}
