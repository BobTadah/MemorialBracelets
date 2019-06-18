<?php

namespace MemorialBracelets\IconOption\Ui\Component\Column;

use Magento\Ui\Component\Listing\Columns\Column;

abstract class Walkable extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        array_walk($dataSource['data']['items'], [$this, 'processItem']);
        return $dataSource;
    }

    abstract public function processItem(array &$item);
}
