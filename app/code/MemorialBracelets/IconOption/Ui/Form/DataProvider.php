<?php

namespace MemorialBracelets\IconOption\Ui\Form;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MemorialBracelets\IconOption\Model\IconOption;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption\Collection;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    const PERSISTOR_KEY_OPTION_ICON = 'option_icon';

    /** @var Collection */
    protected $collection;

    /** @var DataPersistorInterface */
    protected $dataPersistor;

    /** @var array */
    protected $loadedData;

    /** @var PoolInterface */
    protected $modifierPool;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $persistor,
        PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $persistor;
        $this->modifierPool = $modifierPool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var IconOption $icon */
        foreach ($items as $icon) {
            $this->loadedData[$icon->getId()]['general'] = $icon->getData();
        }

        $data = $this->dataPersistor->get(self::PERSISTOR_KEY_OPTION_ICON);
        if (!empty($data)) {
            $icon = $this->collection->getNewEmptyItem();
            $icon->setData($data);
            $this->loadedData[$icon->getId()]['general'] = $icon->getData();
            $this->dataPersistor->clear(self::PERSISTOR_KEY_OPTION_ICON);
        }

        if (empty($this->loadedData)) {
            $this->loadedData = [];
        }

        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->loadedData = $modifier->modifyData($this->loadedData);
        }

        return $this->loadedData;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
