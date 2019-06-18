<?php

namespace MemorialBracelets\IconOption\Ui\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\DataProvider\AbstractDataProvider;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    public function __construct(
        $name,
        $dbField,
        $requestField,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $dbField, $requestField, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
