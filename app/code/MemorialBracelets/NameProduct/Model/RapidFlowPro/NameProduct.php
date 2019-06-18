<?php

namespace MemorialBracelets\NameProduct\Model\RapidFlowPro;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use MemorialBracelets\NameProduct\Model\ResourceModel\Product\Link;
use Psr\Log\LoggerInterface;
use Unirgy\RapidFlow\Model\Profile;

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
 * @property Select           $_select
 * @property LoggerInterface  $_logger
 * @property Profile          $_profile
 *
 * @property array            $_rowTypeFields
 * @property array            $_skus (SKU => ID)
 */
class NameProduct extends \Unirgy\RapidFlowPro\Model\ResourceModel\ProductExtra
{
    protected $_dataType = 'nameproduct_extra';

    private $_cpnoPositionAttributeId;

    public function _exportInitCPNO()
    {
        $linkNum = Link::LINK_TYPE_NAME;
        $attrIdOn = 'attrPosId.link_type_id='.$linkNum.' AND attrPosId.product_link_attribute_code="position"';
        $attrPosOn = 'attrPosValue.product_link_attribute_id=attrPosId.product_link_attribute_id '.
            'AND attrPosValue.link_id=main.link_id';

        $this->_select = $this->_read->select()->from(['main' => $this->_t('catalog_product_link')])
            ->joinLeft(['e1' => $this->_t('catalog_product_entity')], 'e1.entity_id=main.product_id')
            ->joinLeft(['e2' => $this->_t('catalog_product_entity')], 'e2.entity_id=main.linked_product_id')
            ->joinLeft(['attrPosId' => $this->_t('catalog_product_link_attribute')], $attrIdOn)
            ->joinLeft(['attrPosValue' => $this->_t('catalog_product_link_attribute_int')], $attrPosOn)
            ->columns(['sku' => 'e1.sku', 'linked_sku' => 'e2.sku', 'position' => 'attrPosValue.value'])
            ->where('main.link_type_id = ?', Link::LINK_TYPE_NAME);
    }

    public function _deleteRowCPNO($row)
    {
        // Verify, ##-CPNO,SKU,LINKED_SKU
        if ($row[0] != '-CPNO') {
            throw new \InvalidArgumentException('Bad Row - Should be -CPNO, was: '.$row[0]);
        }
        if (sizeof($row) < 3) {
            throw new \InvalidArgumentException('Invalid row format (did not contain 3 points)');
        }

        $skuColumn = 1;
        $linkedSkuColumn = 2;

        $productId = $this->_getIdBySku($row[$skuColumn]);
        $linkProductId = $this->_getIdBySku($row[$linkedSkuColumn]);

        $linkTypeId = Link::LINK_TYPE_NAME;

        $where = sprintf(
            'product_id = %1$d AND linked_product_id = %2$d AND link_type_id = %3$d',
            $productId,
            $linkProductId,
            $linkTypeId
        );

        $this->_write->beginTransaction();
        $i = $this->_write->delete($this->_t('catalog_product_link'), $where);
        // We don't have to delete from the attribute tables thanks to foreign keys <3
        if ($i > 1) {
            $this->_write->rollBack();
            throw new \RuntimeException('Attempted to delete more than one entry at a time');
        }
        $this->_write->commit();

        if ($i < 1) {
            $this->_profile->addValue('num_warnings');
            $this->_profile->getLogger()->warning('Link not found to delete');
            return self::IMPORT_ROW_RESULT_NOCHANGE;
        }

        return self::IMPORT_ROW_RESULT_SUCCESS;
    }

    public function _importRowCPNO($row)
    {
        // Verify, ##CPNO,SKU,LINKED_SKU
        if (!in_array($row[0], ['CPNO', '+CPNO'])) {
            throw new \InvalidArgumentException('Bad Row - Should be CPNO or +CPNO, was: '.$row[0]);
        }
        if (sizeof($row) < 3) {
            throw new \InvalidArgumentException('Invalid row format (did not contain 3 points)');
        }

        $skuColumn = 1;
        $linkedSkuColumn = 2;
        $positionColumn = 3;

        $productId = $this->_getIdBySku($row[$skuColumn]);
        $linkProductId = $this->_getIdBySku($row[$linkedSkuColumn]);
        $position = isset($row[$positionColumn]) ? $row[$positionColumn] : 0;

        $linkTypeId = Link::LINK_TYPE_NAME;

        $insert = [
            'product_id'        => $productId,
            'linked_product_id' => $linkProductId,
            'link_type_id'      => $linkTypeId,
        ];

        $this->_write->insertOnDuplicate($this->_t('catalog_product_link'), $insert);

        $linkIdSelect = $this->_read->select()
            ->from($this->_t('catalog_product_link'), ['link_id'])
            ->where('product_id = ?', $productId)
            ->where('linked_product_id = ?', $linkProductId)
            ->where('link_type_id = ?', $linkTypeId);

        $linkId = $this->_read->fetchOne($linkIdSelect);

        $posAttrId = $this->getCPNOPositionAttributeId();

        $currentPositionSelect = $this->_read->select()
            ->from($this->_t('catalog_product_link_attribute_int'), ['value'])
            ->where('product_link_attribute_id = ?', $posAttrId)
            ->where('link_id = ?', $linkId);

        $currentPosition = $this->_read->fetchOne($currentPositionSelect);

        if ($currentPosition == $position) {
            return self::IMPORT_ROW_RESULT_NOCHANGE;
        }

        $this->_write->insertOnDuplicate(
            $this->_t('catalog_product_link_attribute_int'),
            [
                'product_link_attribute_id' => $posAttrId,
                'link_id'                   => $linkId,
                'value'                     => $position,
            ],
            ['value']
        );

        return self::IMPORT_ROW_RESULT_SUCCESS;
    }

    private function getCPNOPositionAttributeId()
    {
        if (!isset($this->_cpnoPositionAttributeId)) {
            $select = $this->_read->select()
                ->from($this->_t('catalog_product_link_attribute'))
                ->columns('product_link_attribute_id')
                ->where('link_type_id = ?', Link::LINK_TYPE_NAME)
                ->where('product_link_attribute_code = ?', 'position');

            $this->_cpnoPositionAttributeId = $this->_read->fetchOne($select);
        }
        return $this->_cpnoPositionAttributeId;
    }
}
