<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\SalesRep\Ui\DataProvider\Form;

use \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class CommissionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\Customer\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var
     */
    protected $loadedData;

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        if (isset($data['config']['submit_url'])) {
            $data['config']['submit_url'] = $this->urlBuilder->getUrl('*/*/save', ['id' => $this->request->getParam('id')]);
        }
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->pool = $pool;
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
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }

        $data = $this->dataPersistor->get('attached_customer');
        if (!empty($data) && empty($this->loadedData)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data->getData());
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('attached_customer');
        }
        $this->addDataModifiers();

        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     */
    public function addDataModifiers()
    {
        if(!empty($this->loadedData)) {
            /** @var ModifierInterface $modifier */
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                foreach ($this->loadedData as $id => $data) {
                    $this->loadedData[$id] = $modifier->modifyData($data);
                }
            }
        }

        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
