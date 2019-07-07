<?php

namespace IWD\OrderGrid\Model\Config\Source\Order;

use \Magento\Framework\Option\ArrayInterface;

class MassAction implements ArrayInterface
{
    /**
     * Options getter
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'delete'     , 'label' => __('Delete Order(s)')],
            ['value' => 'status', 'label' => __('Change status')],
            ['value' => 'iwd_print_orders' , 'label' => __('Print Order(s)')],
            ['value' => 'iwd_invoices', 'label' => __('Invoice')],
            ['value' => 'iwd_ship', 'label' => __('Ship')],
            ['value' => 'iwd_invoice_create_print', 'label' => __('Invoice and Print')],
            ['value' => 'iwd_invoice_ship_create', 'label' => __('Invoice and Ship')],
            ['value' => 'iwd_ship_create_print', 'label' => __('Ship and Print')],
            ['value' => 'iwd_invoice_ship_create_print', 'label' => __('Invoice and Ship and Print')],
            ['value' => 'iwd_invoice_ship_print', 'label' => __('Print Invoice and Ship')],
            ['value' => 'iwd_order_resend', 'label' => __('Resend Order')],
            ['value' => 'iwd_invoice_resend', 'label' => __('Resend Invoice')],
            ['value' => 'iwd_ship_resend', 'label' => __('Resend shipment')],
            ['value' => 'iwd_comment', 'label' => __('Add comment')],
        ];
    }


    /**
     *@return array[]
     */
    public function toArray()
    {
        return [
            'delete' => __('Delete Order(s)'),
            'status' => __('Change status'),
            'iwd_print_orders' => __('Print Order(s)'),
            'iwd_invoices' => __('Invoice'),
            'iwd_ship' => __('Ship'),
            'iwd_invoice_create_print' => __('Invoice and Print'),
            'iwd_invoice_ship_create' => __('Invoice and Ship'),
            'iwd_ship_create_print' => __('Ship and Print'),
            'iwd_invoice_ship_create_print' => __('Invoice and Ship and Print'),
            'iwd_invoice_ship_print' => __('Print Invoice and Ship'),
            'iwd_order_resend' => __('Resend Order'),
            'iwd_invoice_resend' => __('Resend Invoice'),
            'iwd_ship_resend' => __('Resend shipment'),
            'iwd_comment' => __('Add comment'),
        ];
    }
}
