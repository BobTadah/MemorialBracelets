<?php

namespace MemorialBracelets\CharmOption\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /** @var PoolInterface */
    protected $modifiers;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        DataPersistorInterface $dataPersistor,
        PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $blockCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->modifiers = $modifierPool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        foreach ($this->modifiers->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \MemorialBracelets\CharmOption\Model\CharmOption $option */
        foreach ($items as $option) {
            $this->loadedData[$option->getId()]['general'] = $option->getData();
        }

        $data = $this->dataPersistor->get('option_charm');
        if (!empty($data)) {
            $option = $this->collection->getNewEmptyItem();
            $option->setData($data);
            $this->loadedData[$option->getId()]['general'] = $option->getData();
            $this->dataPersistor->clear('option_charm');
        }

        if (empty($this->loadedData)) {
            $this->loadedData = [];
        }

        foreach ($this->modifiers->getModifiersInstances() as $modifier) {
            $this->loadedData = $modifier->modifyData($this->loadedData);
        }

        return $this->loadedData;
    }
}
