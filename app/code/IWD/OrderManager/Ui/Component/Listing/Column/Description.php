<?php

namespace IWD\OrderManager\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Items
 * @package IWD\OrderManager\Ui\Component\Listing\Column
 */
class Description extends Column
{


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['description'])) {
                    $item['description'] = filter_var($item['description'], FILTER_SANITIZE_STRING);
                }
            }
        }

        return $dataSource;
    }
}
