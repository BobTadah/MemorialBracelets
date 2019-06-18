<?php

namespace MemorialBracelets\BraceletPreview\Model;

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
    protected $_dataType = 'product_extra_braceletpreview';

    public function _exportInitCPCOBP()
    {
        $this->_select = $this->_read->select()->from(['main' => $this->_t('mb_preview_product_option_preview_piece')])
            ->joinLeft(['opt' => $this->_t('catalog_product_option')], 'opt.option_id = main.option_id')
            ->joinLeft(
                ['opt_title' => $this->_t('catalog_product_option_title')],
                'opt_title.option_id = main.option_id AND opt_title.store_id=0'
            )
            ->joinLeft(['prod' => $this->_t('catalog_product_entity')], 'prod.entity_id = opt.product_id')
            ->columns(
                [
                    'sku' => 'prod.sku',
                    'default_title' => 'opt_title.title',
                    'piece' => 'main.piece'
                ]
            );
    }

    public function _deleteRowCPCOBP($row)
    {
        if ($row[0] != '-CPCOBP') {
            throw new \InvalidArgumentException('Bad Row - Should be -CPCOBP, was: ' . $row[0]);
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

        $i = $this->_write->delete(
            $this->_t('mb_preview_product_option_preview_piece'),
            ['option_id = ?' => $optionId]
        );
        return $i ? self::IMPORT_ROW_RESULT_SUCCESS : self::IMPORT_ROW_RESULT_NOCHANGE;
    }

    public function _importRowCPCOBP($row)
    {
        if (!in_array($row[0], ['CPCOBP', '+CPCOBP'])) {
            throw new \InvalidArgumentException('Bad Row - Should be CPCOBP or +CPCOBP, was: ' . $row[0]);
        }
        if (sizeof($row) < 4) {
            throw new \InvalidArgumentException('Invalid row format (did not contain 4 points)');
        }

        $sku = $row[1];
        $defaultTitle = $row[2];
        $piece = $row[3];

        $productId = $this->_getIdBySku($sku);
        $optionId = $this->getOptionIdByTitle($productId, $defaultTitle);

        if (!$optionId) {
            throw new \InvalidArgumentException('Option "' . $defaultTitle . '" to attach updates to does not exist');
        }

        $select = $this->_read->select()
            ->from(['main' => $this->_t('mb_preview_product_option_preview_piece')])
            ->where('main.option_id = ?', $optionId);
        $one = $this->_read->fetchOne($select);
        if (!empty($one)) {
            $this->_write->update(
                $this->_t('mb_preview_product_option_preview_piece'),
                ['piece' => $piece],
                ['option_id = ?' => $optionId]
            );
        } else {
            $this->_write->insert(
                $this->_t('mb_preview_product_option_preview_piece'),
                ['piece' => $piece, 'option_id' => $optionId]
            );
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
}
