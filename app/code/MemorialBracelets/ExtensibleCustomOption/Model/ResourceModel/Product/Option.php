<?php

namespace MemorialBracelets\ExtensibleCustomOption\Model\ResourceModel\Product;

class Option extends \Magento\Catalog\Model\ResourceModel\Product\Option
{
    public function shouldRecordPrice($type)
    {
        switch ($type) {
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FIELD:
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA:
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FILE:
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE:
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME:
            case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME:
                return true;
            default:
                return false;
        }
    }

    /**
     * Save value prices
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _saveValuePrices(\Magento\Framework\Model\AbstractModel $object)
    {
        $priceTable = $this->getTable('catalog_product_option_price');
        $connection = $this->getConnection();

        /*
         * Better to check param 'price' and 'price_type' for saving.
         * If there is not price skip saving price
         */

        if ($this->shouldRecordPrice($object->getType())) {
            //save for store_id = 0
            if (!$object->getData('scope', 'price')) {
                $statement = $connection->select()->from(
                    $priceTable,
                    'option_id'
                )->where(
                    'option_id = ?',
                    $object->getId()
                )->where(
                    'store_id = ?',
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );
                $optionId = $connection->fetchOne($statement);

                if ($optionId) {
                    $data = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject(
                            ['price' => $object->getPrice(), 'price_type' => $object->getPriceType()]
                        ),
                        $priceTable
                    );

                    $connection->update(
                        $priceTable,
                        $data,
                        [
                            'option_id = ?' => $object->getId(),
                            'store_id  = ?' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    );
                } else {
                    $data = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject(
                            [
                                'option_id' => $object->getId(),
                                'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                'price' => $object->getPrice(),
                                'price_type' => $object->getPriceType(),
                            ]
                        ),
                        $priceTable
                    );
                    $connection->insert($priceTable, $data);
                }
            }

            $scope = (int)$this->_config->getValue(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($object->getStoreId() != '0' && $scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE) {
                $baseCurrency = $this->_config->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    'default'
                );

                $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
                if (is_array($storeIds)) {
                    foreach ($storeIds as $storeId) {
                        if ($object->getPriceType() == 'fixed') {
                            $storeCurrency = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
                            $rate = $this->_currencyFactory->create()->load($baseCurrency)->getRate($storeCurrency);
                            if (!$rate) {
                                $rate = 1;
                            }
                            $newPrice = $object->getPrice() * $rate;
                        } else {
                            $newPrice = $object->getPrice();
                        }

                        $statement = $connection->select()->from(
                            $priceTable
                        )->where(
                            'option_id = ?',
                            $object->getId()
                        )->where(
                            'store_id  = ?',
                            $storeId
                        );

                        if ($connection->fetchOne($statement)) {
                            $data = $this->_prepareDataForTable(
                                new \Magento\Framework\DataObject(
                                    ['price' => $newPrice, 'price_type' => $object->getPriceType()]
                                ),
                                $priceTable
                            );

                            $connection->update(
                                $priceTable,
                                $data,
                                ['option_id = ?' => $object->getId(), 'store_id  = ?' => $storeId]
                            );
                        } else {
                            $data = $this->_prepareDataForTable(
                                new \Magento\Framework\DataObject(
                                    [
                                        'option_id' => $object->getId(),
                                        'store_id' => $storeId,
                                        'price' => $newPrice,
                                        'price_type' => $object->getPriceType(),
                                    ]
                                ),
                                $priceTable
                            );
                            $connection->insert($priceTable, $data);
                        }
                    }
                }
            } elseif ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE && $object->getData('scope', 'price')
            ) {
                $connection->delete(
                    $priceTable,
                    ['option_id = ?' => $object->getId(), 'store_id  = ?' => $object->getStoreId()]
                );
            }
        }

        return $this;
    }

    /** {@inheritdoc} */
    public function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_afterSave($object);
    }

    /** {@inheritdoc} */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_beforeSave($object);
    }
}
