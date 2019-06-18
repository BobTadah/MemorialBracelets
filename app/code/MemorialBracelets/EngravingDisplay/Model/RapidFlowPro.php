<?php

namespace MemorialBracelets\EngravingDisplay\Model;

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
    protected $_dataType = 'product_extra_engraving';

    public function _exportInitCPCOES()
    {
        $this->_select = $this->_read->select()->from(['main' => $this->_t('catalog_product_option')])
            ->joinLeft(
                ['opt_title' => $this->_t('catalog_product_option_title')],
                'opt_title.option_id = main.option_id AND opt_title.store_id=0'
            )
            ->joinLeft(['prod' => $this->_t('catalog_product_entity')], 'prod.entity_id = main.product_id')
            ->where('main.type = "engraving"')
            ->columns(
                [
                    'sku' => 'prod.sku',
                    'default_title' => 'opt_title.title',
                    'max_characters' => 'main.max_characters',
                    'lines' => 'main.number_lines',
                    'supportive_message_price' => 'main.supportive_message_price',
                    'name_engraving_price' => 'main.name_engraving_price',
                ]
            );
    }

    public function _deleteRowCPCOES($row)
    {
        if ($row[0] != '-CPCOES') {
            throw new \InvalidArgumentException('Bad Row - Should be -CPCOES, was: ' . $row[0]);
        }

        if (sizeof($row) < 3) {
            throw new \InvalidArgumentException('Invalid row format (did not contain 3 points)');
        }

        $sku = $row[1];
        $defaultTitle = $row[2];

        $productId = $this->_getIdBySku($sku);
        $optionId = $this->getOptionIdByTitle($productId, $defaultTitle);

        if (!$optionId) {
            return self::IMPORT_ROW_RESULT_NOCHANGE;
        }

        $this->_write->update(
            $this->_t('catalog_product_option'),
            [
                'number_lines' => null,
                'supportive_message_price' => null,
                'name_engraving_price' => null,
            ],
            ['option_id = ?' => $optionId]
        );
        return self::IMPORT_ROW_RESULT_SUCCESS;
    }

    public function _importRowCPCOES($row)
    {
        if (!in_array($row[0], ['CPCOES', '+CPCOES'])) {
            throw new \InvalidArgumentException('Bad Row - Should be CPCOES or +CPCOES, was: ' . $row[0]);
        }
        if (sizeof($row) < 3) {
            throw new \InvalidArgumentException('Invalid row format (did not contain 3 points)');
        }

        $sku = $row[1];
        $defaultTitle = $row[2];
        $maxCharacters = isset($row[3]) ? $row[3] : null;
        $lines = isset($row[4]) ? $row[4] : null;
        $suppPrice = isset($row[5]) ? $row[5] : null;
        $namePrice = isset($row[6]) ? $row[6] : null;

        $productId = $this->_getIdBySku($sku);
        $optionId = $this->getOptionIdByTitle($productId, $defaultTitle);

        if (!$optionId) {
            throw new \InvalidArgumentException('Option "' . $defaultTitle . '" to attach updates to does not exist');
        }

        $this->_write->update(
            $this->_t('catalog_product_option'),
            [
                'max_characters' => $maxCharacters,
                'number_lines' => $lines,
                'supportive_message_price' => $suppPrice,
                'name_engraving_price' => $namePrice
            ],
            ['option_id = ?' => $optionId]
        );
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
}
