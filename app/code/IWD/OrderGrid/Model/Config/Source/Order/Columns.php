<?php

namespace IWD\OrderGrid\Model\Config\Source\Order;

use \Magento\Framework\Option\ArrayInterface;

class Columns implements ArrayInterface
{
    /**
     * specific columns
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'iwd_order_product_name'     , 'label' => __('Product items')],
            ['value' => 'iwd_order_product_sku', 'label' => __('Product SKUs')],
            ['value' => 'iwd_invoice_total' , 'label' => __('Total Invoiced')],
            ['value' => 'iwd_order_invoice_number', 'label' => __('Invoices')],
            ['value' => 'iwd_order_shipment_number', 'label' => __('Shipments')],
            ['value' => 'iwd_order_creditmemo_number', 'label' => __('Credit Memos')],
            ['value' => 'iwd_order_comment_last', 'label' => __('Last Order Comment')],
            ['value' => 'iwd_order_comment', 'label' => __('First Order Comment')],
            ['value' => 'iwd_order_discount_amount', 'label' => __('Discount Amount')],
        ];
    }
}