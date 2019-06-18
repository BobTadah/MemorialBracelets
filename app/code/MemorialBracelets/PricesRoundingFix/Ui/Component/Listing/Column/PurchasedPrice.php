<?php

namespace MemorialBracelets\PricesRoundingFix\Ui\Component\Listing\Column;

use Magento\Sales\Ui\Component\Listing\Column\PurchasedPrice as MagentoPurchasedPrice;

/**
 * Class Price
 */
class PurchasedPrice extends MagentoPurchasedPrice
{
    /**
     *
     * We override the default method because for some reason Magento is not calling $this->priceFormatter->format with precision param. So we need to round up to 2 dec.
     *
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = isset($item['order_currency_code']) ? $item['order_currency_code'] : null;
                $item[$this->getData('name')] =
                    $this->priceFormatter->format(
                        $item[$this->getData('name')],
                        false,
                        2,
                        null,
                        $currencyCode
                    );
            }
        }

        return $dataSource;
    }
}
