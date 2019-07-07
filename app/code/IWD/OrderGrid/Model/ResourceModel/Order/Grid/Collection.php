<?php

namespace IWD\OrderGrid\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{

    private $columnsReplace = [
        '`order_items`.`sku`' => 'iwd_order_product_sku',
        '`order_items`.`name`' => 'iwd_order_product_name',
        '`invoice`.`increment_id`' => 'iwd_order_invoice_number',
        '`shipment`.`increment_id`' => 'iwd_order_shipment_number',
        '`creditmemo`.`increment_id`' => 'iwd_order_creditmemo_number',
    ];

    protected function _init($model, $resourceModel)
    {
        parent::_init($model, $resourceModel);
        $this
            ->addFilterToMap(
                'iwd_order_product_sku',
                'order_items.sku'
            );
        $this
            ->addFilterToMap(
                'iwd_order_product_name',
                'order_items.name'
            );
        $this
            ->addFilterToMap(
                'iwd_order_invoice_number',
                'invoice.increment_id'
            );
        $this
            ->addFilterToMap(
                'iwd_order_shipment_number',
                'shipment.increment_id'
            );

        $this
            ->addFilterToMap(
                'iwd_order_creditmemo_number',
                'creditmemo.increment_id'
            );
        $this
            ->addFilterToMap(
                'iwd_order_comment_last',
                'order_comments_last.comment'
            );
        $this
            ->addFilterToMap(
                'iwd_order_comment',
                'order_comments.comment'
            );
        $table = $this->getTable('sales_invoice');
        $this
            ->addFilterToMap(
                'iwd_invoice_total',
                new \Zend_Db_Expr('(SELECT ROUND( SUM(' . $table . '.grand_total), 2 ) FROM ' . $table . ' WHERE ' . $table . '.order_id=main_table.entity_id)')
            );
        $this
            ->addFilterToMap(
                'iwd_order_discount_amount',
                new \Zend_Db_Expr('round(sales_orders.discount_amount, 2)')
            );
            
        $this
            ->addFilterToMap(
                'customer_id',
                'main_table.customer_id'
            );

        return $this;
    }

    protected function _beforeLoad()
    {
        $whereCondition = $this->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

        if (is_array($whereCondition) && count($whereCondition)) {

            array_walk($whereCondition, function (&$value) {
                $value = $this->removeSqlAnd($value);
            });

            //issue MAGETWO-81446
            $whereCondition = array_unique($whereCondition);
            if (count($whereCondition)) {
                $this->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                $arr = $this->replaceSql($whereCondition, true);

                if (!empty($arr)) {
                    $arr = $this->addSqlAnd($arr);
                    $this->getSelect()->having(implode(" ", $arr));
                }
                if (isset($this->currentWhere) && is_array($this->currentWhere)) {
                    $this->getSelect()->where(implode(" ", $this->currentWhere));
                }


            }
        }
        return parent::_beforeLoad();
    }

    private function getWhere($whereCondition)
    {
        $arr = [];
        $columnsReplace = $this->columnsReplace;
        $arr = array_filter($whereCondition, function ($value) use ($columnsReplace) {

            foreach ($columnsReplace as $k => $val) {
                if (strpos($value, $k) !== false) {
                    return false;
                }
            }
            return true;

        });

        return $arr;
    }

    private function addSqlAnd($conditionArr)
    {
        if (!empty($conditionArr)) {
            $arr = array_values($conditionArr);

            array_walk($arr, function (&$value, $key) {
                if ($key != 0) {
                    $value = \Magento\Framework\DB\Select::SQL_AND . ' ' . $value;
                }
            });

            return $arr;
        }

        return false;
    }

    private function replaceSql($condition, $isHaving = false)
    {
        $neededArr = [];
        foreach ($this->columnsReplace as $k => $item) {

            if (!$isHaving) {

                foreach ($this->columnsReplace as $k => $item) {
                    array_walk($condition, function (&$value) use ($item, $k) {
                        $value = str_replace($item, $k, $value);
                    });
                }
                $neededArr = array_merge($neededArr, $condition);
            } else {
                $where = $this->getWhere($condition);
                $this->currentWhere = $this->addSqlAnd($where);
                $having = array_filter($condition, function ($value) use ($k) {
                    return strpos($value, $k);
                });
                $neededArr = array_merge($neededArr, $having);
            }

        }


        if ($isHaving && !empty($neededArr)) {
            foreach ($this->columnsReplace as $k => $item) {
                array_walk($neededArr, function (&$value) use ($item, $k) {
                    $value = str_replace($k, $item, $value);
                });
            }
        }

        return $neededArr;
    }

    private function removeSqlAnd($condition)
    {
        if (strtok($condition, " ") == \Magento\Framework\DB\Select::SQL_AND) {
            return substr(strstr($condition, " "), 1);
        }

        return $condition;

    }


    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $havingCondition = $this->getSelect()->getPart(\Magento\Framework\DB\Select::HAVING);
        if (count($havingCondition)) {
            $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
            $arr = $this->replaceSql($havingCondition);
            $arr = array_unique($arr);
            $countSelect->where(implode(" ", $arr));
        }

        return $countSelect;
    }


}
