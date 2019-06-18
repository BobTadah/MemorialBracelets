<?php

namespace MemorialBracelets\IconOption\Plugin;

use IWD\OrderManager\Model\Pdf\Order;

/**
 * Class PdfPrint
 * @package MemorialBracelets\IconOption\Plugin
 */
class PdfPrint
{
    /**
     * Prepare order item options by setting its print value.
     *
     * @param Order $subject
     * @param \Magento\Sales\Model\Order[] $orders
     * @return \Magento\Sales\Model\Order[] $orders
     */
    public function beforeGetPdf(Order $subject, $orders = [])
    {
        foreach ($orders as $order) {
            foreach ($order->getAllItems() as $item) {
                //Check that order item hast options.
                if (isset($item->getProductOptions()['options'])) {
                    $itemOptions = $item->getProductOptions();
                    foreach ($itemOptions['options'] as $key => $option) {
                        //Find iconpicker option.
                        if (isset($option['option_type'])
                            &&
                            ($option['option_type'] == 'iconpicker' || $option['option_type'] == 'picker')
                        ) {
                            $option['print_value'] = $option['value'];
                            //Remove old option data.
                            unset($itemOptions['options'][$key]);
                            //Add updated data.
                            $itemOptions['options'][] = $option;
                        }
                    }
                    //Set updated options to object.
                    $item->setProductOptions($itemOptions);
                }
            }
        }

        return [$orders];
    }
}
