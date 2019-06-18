<?php

namespace MemorialBracelets\SwatchOption\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use MemorialBracelets\ExtensibleCustomOption\Model\ResourceModel\Product\Option\Value;

class OptionValueSaveModifier
{
    protected $_config;
    protected $_storeManager;

    public function __construct(ScopeConfigInterface $config, StoreManagerInterface $storeManager)
    {
        $this->_config = $config;
        $this->_storeManager = $storeManager;
    }

    // @codingStandardsIgnoreStart
    public function around_afterSave(Value $subject, callable $proceed, AbstractModel $object)
    {
        // @codingStandardsIgnoreEnd
        $result = $proceed($object);
        $this->saveValueSwatchData($subject, $object);

        return $result;
    }

    public function aroundDuplicate(
        Value $subject,
        callable $proceed,
        \Magento\Catalog\Model\Product\Option\Value $object,
        $oldOptionId,
        $newOptionId
    ) {
        $result = $proceed($object, $oldOptionId, $newOptionId);

        // We do this after, so the main tables have already been duplicated

        $connection = $subject->getConnection();
        $select = $connection->select()->from($subject->getMainTable())->where('option_id = ?', $oldOptionId);
        $valueData = $connection->fetchAll($select);

        $idMap = [];
        foreach ($valueData as $data) {
            $sort = $data['sort_order'];
            if (!isset($idMap[$sort])) {
                $idMap[$sort] = [];
            }
            $idMap[$sort]['old_id'] = $data['option_type_id'];
        }

        $select = $connection->select()->from($subject->getMainTable())->where('option_id = ?', $newOptionId);
        $valueData = $connection->fetchAll($select);

        foreach ($valueData as $data) {
            $sort = $data['sort_order'];
            $idMap[$sort]['new_id'] = $data['option_type_id'];
        }

        foreach ($idMap as $valueCond) {
            $oldTypeId = $valueCond['old_id'];
            $newTypeId = $valueCond['new_id'];

            $swatchTable = $subject->getTable('mb_swatch_product_option_type_swatch');
            $columns = [new \Zend_Db_Expr($newTypeId), 'store_id', 'swatch_abbr', 'swatch_color', 'image'];

            $select = $connection->select()
                ->from($swatchTable, [])
                ->where('option_type_id = ?', $oldTypeId)
                ->columns($columns);

            $insertSelect = $connection->insertFromSelect(
                $select,
                $swatchTable,
                ['option_type_id', 'store_id', 'swatch_abbr', 'swatch_color', 'image']
            );
            $connection->query($insertSelect);
        }

        return $result;
    }

    protected function saveValueSwatchData(Value $subject, AbstractModel $object)
    {
        $swatchTable = $subject->getTable('mb_swatch_product_option_type_swatch');

        $image = $object->getData('image');
        if (is_array($image)) {
            $image = $image[0]['file'];
            $object->setData('image', $image);
        }

        $select = $subject->getConnection()->select()
            ->from($swatchTable, 'option_type_id')
            ->where('option_type_id = ?', (int)$object->getId())
            ->where('store_id = ?', Store::DEFAULT_STORE_ID);

        $optionTypeId = $subject->getConnection()->fetchOne($select);

        $exists = !!$optionTypeId;

        $values = [];

        if ($object->getData('swatch_abbr')) {
            $values['swatch_abbr'] = $object->getData('swatch_abbr');
        }
        if ($object->getData('swatch_color')) {
            $values['swatch_color'] = $object->getData('swatch_color');
        }
        if ($object->getData('image')) {
            $values['image'] = $object->getData('image');
        }

        $deleting = empty($values);

        $insertValues = $values;
        $insertValues['option_type_id'] = (int)$object->getId();

        $scope = (int)$this->_config->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // Insert/Update Default Values
        if (!$deleting) {
            if ($exists && $object->getStoreId() == '0') {
                $where = [
                    'option_type_id = ?' => $optionTypeId,
                    'store_id = ?'       => Store::DEFAULT_STORE_ID,
                ];

                $subject->getConnection()->update($swatchTable, $values, $where);
            } elseif (!$exists) {
                $subject->getConnection()->insert(
                    $swatchTable,
                    array_merge($insertValues, ['store_id' => Store::DEFAULT_STORE_ID])
                );
            }
        }

        // Insert/Update Store Values
        if ($scope == Store::PRICE_SCOPE_WEBSITE && !$deleting && $object->getStoreId() != Store::DEFAULT_STORE_ID) {
            $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
            if (is_array($storeIds)) {
                foreach ($storeIds as $storeId) {
                    $select = $subject->getConnection()->select()
                        ->from($swatchTable, 'option_type_id')
                        ->where('option_type_id = ?', (int)$object->getId())
                        ->where('store_id = ?', (int)$storeId);

                    $optionTypeId = $subject->getConnection()->fetchOne($select);

                    if ($optionTypeId) {
                        $where = ['option_type_id = ?', $optionTypeId, 'store_id = ?', (int)$storeId];
                        $subject->getConnection()->update($swatchTable, $values, $where);
                    } else {
                        $subject->getConnection()->insert(
                            $swatchTable,
                            array_merge($insertValues, ['store_id' => (int)$storeId])
                        );
                    }
                }
            }
        }

        if ($deleting && $scope == Store::PRICE_SCOPE_WEBSITE) {
            $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
            foreach ($storeIds as $storeId) {
                $where = [
                    'option_type_id = ?' => (int)$object->getId(),
                    'store_id = ?'       => $storeId,
                ];
                $subject->getConnection()->delete($swatchTable, $where);
            }
        }
    }
}
