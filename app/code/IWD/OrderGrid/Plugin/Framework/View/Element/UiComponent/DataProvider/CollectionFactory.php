<?php

namespace IWD\OrderGrid\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as MagentoCollectionFactory;
use \IWD\OrderGrid\Helper\Config as ConfigHelper;
use \Magento\Framework\App\ResourceConnection;

/**
 * Class CollectionFactory
 * @package IWD\OrderGrid\Plugin\Framework\View\Element\UiComponent\DataProvider
 */
class CollectionFactory
{
    /**
     * @var
     */
    private $select;
    /**
     * @var \IWD\OrderGrid\Helper\Config
     */
    private $configHelper;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;


    /**
     * CollectionFactory constructor.
     * @param ConfigHelper $configHelper
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ConfigHelper $configHelper,
        ResourceConnection $resourceConnection
    )
    {
        $this->configHelper = $configHelper;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param MagentoCollectionFactory $subject
     * @param \Closure $proceed
     * @param $requestName
     * @return mixed
     */
    public function aroundGetReport(
        MagentoCollectionFactory $subject,
        \Closure $proceed,
        $requestName
    )
    {
        $result = $proceed($requestName);

        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection) {
                /**
                 * @var $result \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
                 */
                $this->select = $result->getSelect();
                $this->addProductData();
                $this->addFirstOrderComment();
                $this->addLastOrderComment();
                $this->addInvoiceTotal();
                $this->addInvoice();
                $this->addShipment();
                $this->addCreditmemo();
                $this->addDiscountAmount();
                $this->select->group('main_table.entity_id');
            }
        }

        return $result;
    }


    public function addProductData()
    {
        if ($this->configHelper->isActiveColumn('iwd_order_product_sku') || $this->configHelper->isActiveColumn('iwd_order_product_name')) {
            $this->select->joinLeft(
                ['order_items' => $this->resourceConnection->getTableName('sales_order_item')],
                'main_table.entity_id=order_items.order_id AND order_items.parent_item_id IS NULL',
                [
                    'iwd_order_product_sku' => new \Zend_Db_Expr('group_concat( DISTINCT replace( order_items.sku, ",","") )'),
                    'iwd_order_product_name' => new \Zend_Db_Expr('group_concat( DISTINCT replace( order_items.name, ",","") )'),
                    'iwd_order_product_id' => new \Zend_Db_Expr('group_concat( DISTINCT order_items.product_id)'),
                    'iwd_order_product_group' => new \Zend_Db_Expr('group_concat( DISTINCT CONCAT(order_items.product_id, ",", replace(order_items.sku,",",""), ",",  replace(order_items.name, ",", "")) )'),
                ]
            );
        }
    }

    public function addFirstOrderComment()
    {
        if ($this->configHelper->isActiveColumn('iwd_order_comment')) {
            $this->select->joinLeft(
                ['order_comments' => $this->resourceConnection->getTableName('sales_order_status_history')],
                'main_table.entity_id=order_comments.parent_id AND order_comments.entity_name = "order" AND order_comments.comment IS NOT NULL',
                ['iwd_order_comment' => 'order_comments.comment']
            );
        }
    }

    public function addLastOrderComment()
    {
        if ($this->configHelper->isActiveColumn('iwd_order_comment_last')) {
            $table = $this->resourceConnection->getTableName("sales_order_status_history");
            $this->select->joinLeft(
                ['order_comments_last' => $table],
                "main_table.entity_id=order_comments_last.parent_id AND order_comments_last.entity_id =
                    (
                    SELECT
                        $table.entity_id
                    FROM
                        $table
                    WHERE
                           $table.comment IS NOT NULL
                           AND $table.parent_id=main_table.entity_id
                           AND $table.entity_name = 'order'
                    ORDER BY $table.entity_id DESC LIMIT 1 )",
                ['iwd_order_comment_last' => 'order_comments_last.comment']
            );
        }
    }

    public function addInvoiceTotal()
    {
        if ($this->configHelper->isActiveColumn('iwd_invoice_total')) {
            $table = $this->resourceConnection->getTableName('sales_invoice');
            $this->select->columns(
                ['iwd_invoice_total' =>
                    new \Zend_Db_Expr('(SELECT ROUND( SUM(' . $table . '.grand_total), 2 ) FROM ' . $table . ' WHERE ' . $table . '.order_id=main_table.entity_id)')]
            );
        }
    }

    public function addInvoice()
    {
        if ($this->configHelper->isActiveColumn('iwd_order_invoice_number')) {
            $this->select->joinLeft(
                ['invoice' => $this->resourceConnection->getTableName('sales_invoice')],
                'main_table.entity_id=invoice.order_id',
                [
                    'iwd_order_invoice_number' => new \Zend_Db_Expr('group_concat(DISTINCT invoice.increment_id separator ",")'),
                    'iwd_order_invoice_id' => new \Zend_Db_Expr('group_concat(DISTINCT invoice.entity_id separator ",")'),
                ]
            );
        }
    }

    public function addShipment()
    {
        if ($this->configHelper->isActiveColumn('iwd_order_shipment_number')) {
            $this->select->joinLeft(
                ['shipment' => $this->resourceConnection->getTableName('sales_shipment')],
                'main_table.entity_id=shipment.order_id',
                [
                    'iwd_order_shipment_number' => new \Zend_Db_Expr('group_concat(DISTINCT shipment.increment_id separator ",")'),
                    'iwd_order_shipment_id' => new \Zend_Db_Expr('group_concat(DISTINCT shipment.entity_id separator ",")'),
                ]
            );
        }
    }

    public function addCreditmemo()
    {
        if ($this->configHelper->isActiveColumn('iwd_order_creditmemo_number')) {
            $this->select->joinLeft(
                ['creditmemo' => $this->resourceConnection->getTableName('sales_creditmemo')],
                'main_table.entity_id=creditmemo.order_id',
                [
                    'iwd_order_creditmemo_number' => new \Zend_Db_Expr('group_concat(DISTINCT creditmemo.increment_id separator ",")'),
                    'iwd_order_creditmemo_id' => new \Zend_Db_Expr('group_concat(DISTINCT creditmemo.entity_id separator ",")'),
                ]
            );
        }
    }

    public function addDiscountAmount()
    {
        if ($this->configHelper->isActiveColumn('iwd_order_discount_amount')) {
            $this->select->joinLeft(
                ['sales_orders' => $this->resourceConnection->getTableName('sales_order')],
                'main_table.entity_id=sales_orders.entity_id',
                [
                    'iwd_order_discount_amount' => new \Zend_Db_Expr('round(sales_orders.discount_amount, 2)')
                ]
            );
        }
    }
}
