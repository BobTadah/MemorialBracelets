<?php

namespace MemorialBracelets\SupportiveMessages\Model;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use MemorialBracelets\SupportiveMessages\Model\ResourceModel\SupportiveMessage\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $collection;

    protected $dataPersistor;

    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $persistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $persistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var SupportiveMessage $message */
        foreach ($items as $message) {
            $this->loadedData[$message->getId()]['general'] = $message->getData();
        }

        $data = $this->dataPersistor->get('supportivemessage');
        if (!empty($data)) {
            $message = $this->collection->getNewEmptyItem();
            $message->setData($data);
            $this->loadedData[$message->getId()]['general'] = $message->getData();
            $this->dataPersistor->clear('supportivemessage');
        }

        return $this->loadedData;
    }
}
