<?php

namespace MemorialBracelets\BraceletPreview\Plugin;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;
use MemorialBracelets\BraceletPreview\Helper\AddPreviewPieceToResult;

class OrderItemSaveListener implements ObserverInterface
{
    protected $optionCollectionFactory;
    protected $processor;

    public function __construct(CollectionFactory $optionCollectionFactory, AddPreviewPieceToResult $processor)
    {
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->processor = $processor;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getData('item');

        $options = $item->getData('product_options');
        $serialized = false;
        if (!is_array($options)) {
            $serialized = true;
            $options = unserialize($options);
        }

        if (!isset($options['options'])) {
            return;
        }

        $optionIds = array_map(
            function ($option) {
                return $option['option_id'];
            },
            $options['options']
        );

        $collection = $this->optionCollectionFactory->create();
        $collection->addIdsToFilter($optionIds);
        $this->processor->addPieceToResult($collection);

        $dbOptions = $collection->getItems();

        foreach ($options['options'] as &$option) {
            if (isset($dbOptions[$option['option_id']])) {
                $dbOption = $dbOptions[$option['option_id']];
                $option['bracelet_piece'] = $dbOption->hasData('piece') ? $dbOption->getData('piece') : 'none';
            }
        }
        $item->setData('product_options', $options);
    }
}
