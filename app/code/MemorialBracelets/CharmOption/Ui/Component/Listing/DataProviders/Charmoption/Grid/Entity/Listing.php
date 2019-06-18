<?php
namespace MemorialBracelets\CharmOption\Ui\Component\Listing\DataProviders\Charmoption\Grid\Entity;

class Listing extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
