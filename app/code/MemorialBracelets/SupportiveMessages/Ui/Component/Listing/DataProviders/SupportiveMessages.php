<?php

namespace MemorialBracelets\SupportiveMessages\Ui\Component\Listing\DataProviders;

use Magento\Ui\DataProvider\AbstractDataProvider;
use MemorialBracelets\SupportiveMessages\Model\ResourceModel\SupportiveMessage\CollectionFactory as Factory;

class SupportiveMessages extends AbstractDataProvider
{
    public function __construct($name, $dbField, $requestField, Factory $factory, array $meta = [], array $data = [])
    {
        parent::__construct($name, $dbField, $requestField, $meta, $data);
        $this->collection = $factory->create();
    }
}
