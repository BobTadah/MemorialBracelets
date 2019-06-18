<?php

namespace MemorialBracelets\SwatchOption\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlowPro\Model\ResourceModel\ProductExtra;

/**
 * Class NameProduct
 * @package MemorialBracelets\NameProduct\Model\RapidFlowPro
 *
 * @method string _getIdBySku(string $sku)
 * @method string _t(string $table)
 *
 * @property AdapterInterface $_read
 * @property AdapterInterface $_write
 *
 * @property Select $_select
 * @property LoggerInterface $_logger
 * @property Profile $_profile
 *
 * @property array $_rowTypeFields
 * @property array $_skus (SKU => ID)
 */
class RapidFlowPro extends ProductExtra
{
    protected $_dataType = 'product_extraswatch';

    public function _exportInitCPCOSS()
    {
        $this->_select = $this->_read->select()->from(['main' => $this->_t('mb_swatch_product_option_type_swatch')])
            ->joinLeft(
                ['sel' => $this->_t('catalog_product_option_type_value')],
                'sel.option_type_id = main.option_type_id'
            )
            ->joinLeft(
                ['sel_title' => $this->_t('catalog_product_option_type_title')],
                'sel_title.option_type_id = sel.option_type_id AND sel_title.store_id=0'
            )
            ->joinLeft(
                ['sel_price' => $this->_t('catalog_product_option_type_price')],
                'sel_price.option_type_id = sel.option_type_id AND sel_price.store_id=0'
            )
            ->joinLeft(['opt' => $this->_t('catalog_product_option')], 'opt.option_id = sel.option_id')
            ->joinLeft(
                ['opt_title' => $this->_t('catalog_product_option_title')],
                'opt_title.option_id = opt.option_id AND opt_title.store_id=0'
            )
            ->joinLeft(['prod' => $this->_t('catalog_product_entity')], 'prod.entity_id = opt.product_id')
            ->columns(
                [
                    'sku' => 'prod.sku',
                    'default_title' => 'opt_title.title',
                    'selection_default_title' => 'sel_title.title',
                    'abbr' => 'main.swatch_abbr',
                    'color' => 'main.swatch_color',
                    'image' => 'main.image',
                    'selection_sku' => 'sel.sku',
                    'sort_order' => 'sel.sort_order',
                    'price' => 'sel_price.price',
                    'price_type' => 'sel_price.price_type'
                ]
            );
    }

    public function _deleteRowCPCOSS($row)
    {
        if ($row[0] != '-CPCOSS') {
            throw new \InvalidArgumentException('Bad Row - Should be -CPCOSS, was: ' . $row[0]);
        }

        if (sizeof($row) < 4) {
            throw new \InvalidArgumentException('Invalid row format (did not contain 4 points)');
        }

        $sku = $row[1];
        $defaultTitle = $row[2];
        $selectionTitle = $row[3];

        $productId = $this->_getIdBySku($sku);
        $optionId = $this->getOptionIdByTitle($productId, $defaultTitle);
        if (!$optionId) {
            $this->_profile->addValue('num_warnings');
            $this->_profile->getLogger()->warning('Option "' . $defaultTitle . '" not found to delete selection from');
            return self::IMPORT_ROW_RESULT_NOCHANGE;
        }
        $selectionId = $this->getSelectionIdByTitle($optionId, $selectionTitle);
        if (!$selectionId) {
            $this->_profile->addValue('num_warnings');
            $this->_profile->getLogger()->warning('Selection not found to delete');
            return self::IMPORT_ROW_RESULT_NOCHANGE;
        }

        $this->_write->beginTransaction();
        $i = $this->_write->delete(
            $this->_t('catalog_product_option_type_value'),
            ['option_type_id = ?' => $selectionId]
        );
        if ($i > 1) {
            $this->_write->rollBack();
            throw new \RuntimeException('Attempted to delete more than one entry at a time');
        }
        $this->_write->commit();

        return self::IMPORT_ROW_RESULT_SUCCESS;
    }

    public function _importRowCPCOSS($row)
    {
        if (!in_array($row[0], ['CPCOSS', '+CPCOSS'])) {
            throw new \InvalidArgumentException('Bad Row - Should be CPCOSS or +CPCOSS, was: ' . $row[0]);
        }
        if (sizeof($row) < 4) {
            throw new \InvalidArgumentException('Invalid row format (did not contain 4 points)');
        }

        $sku = $row[1];
        $defaultTitle = $row[2];
        $selectionTitle = $row[3];
        $abbr = isset($row[4]) ? $row[4] : null;
        $color = isset($row[5]) ? $row[5] : null;
        $image = isset($row[6]) ? $row[6] : null;
        $selectionSku = isset($row[7]) ? $row[7] : null;
        $sortOrder = isset($row[8]) ? $row[8] : null;
        $price = isset($row[9]) ? $row[9] : null;
        $priceType = isset($row[10]) ? $row[10] : null;

        $productId = $this->_getIdBySku($sku);
        $optionId = $this->getOptionIdByTitle($productId, $defaultTitle);
        if (!$optionId) {
            throw new \InvalidArgumentException('Option "' . $defaultTitle . '" to attach selection to does not exist');
        }
        $selectionId = $this->getSelectionIdByTitle($optionId, $selectionTitle);

        $valueBind = [
            'sku' => $selectionSku,
            'sort_order' => $sortOrder
        ];
        $priceBind = [
            'price' => $price,
            'price_type' => $priceType
        ];
        $swatchBind = [
            'swatch_abbr' => $abbr,
            'swatch_color' => $color,
            'image' => $image
        ];

        if ($selectionId) {
            // update
            $this->_write->update(
                $this->_t('catalog_product_option_type_value'),
                $valueBind,
                ['option_type_id' => $selectionId]
            );
            if (!empty($price) && !empty($priceType)) {
                $this->_write->update(
                    $this->_t('catalog_product_option_type_price'),
                    $priceBind,
                    ['option_type_id' => $selectionId, 'store_id' => 0]
                );
            } else {
                $this->_write->delete(
                    $this->_t('catalog_product_option_type_price'),
                    ['option_type_id = ?' => $optionId, 'store_id=0']
                );
            }
            $this->_write->update(
                $this->_t('mb_swatch_product_option_type_swatch'),
                $swatchBind,
                ['option_type_id' => $selectionId, 'store_id' => 0]
            );
        } else {
            $valueBind['option_id'] = $optionId;
            $this->_write->insert($this->_t('catalog_product_option_type_value'), $valueBind);

            $selectionId = $this->_write->lastInsertId();
            $priceBind['option_type_id'] = $selectionId;
            $priceBind['store_id'] = 0;
            if (!empty($price) && !empty($priceType)) {
                $this->_write->insert($this->_t('catalog_product_option_type_price'), $priceBind);
            }

            $titleBind = [
                'option_type_id' => $selectionId,
                'store_id' => 0,
                'title' => $selectionTitle,
            ];
            $this->_write->insert($this->_t('catalog_product_option_type_title'), $titleBind);

            $swatchBind['option_type_id'] = $selectionId;
            $swatchBind['store_id'] = 0;
            $this->_write->insert($this->_t('mb_swatch_product_option_type_swatch'), $swatchBind);
        }

        return self::IMPORT_ROW_RESULT_SUCCESS;
    }

    private function getOptionIdByTitle($productId, $title)
    {
        $select = $this->_read->select()
            ->from(['opt' => $this->_t('catalog_product_option')])
            ->joinLeft(
                ['opt_title' => $this->_t('catalog_product_option_title')],
                'opt_title.option_id = opt.option_id'
            )
            ->where('opt_title.title = ?', $title)
            ->where('opt.product_id = ?', $productId)
            ->columns(['opt.option_id']);

        return $this->_read->fetchOne($select);
    }

    private function getSelectionIdByTitle($optionId, $title)
    {
        $select = $this->_read->select()
            ->from(['sel' => $this->_t('catalog_product_option_type_value')])
            ->joinLeft(
                ['sel_title' => $this->_t('catalog_product_option_type_title')],
                'sel.option_type_id=sel_title.option_type_id'
            )
            ->where('sel_title.title = ?', $title)
            ->where('sel.option_id = ?', $optionId)
            ->columns(['sel.option_type_id']);

        return $this->_read->fetchOne($select);
    }
}
