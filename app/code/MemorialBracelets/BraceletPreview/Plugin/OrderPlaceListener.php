<?php

namespace MemorialBracelets\BraceletPreview\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MemorialBracelets\BraceletPreview\Helper\AddPreviewPieceToResult;

class OrderPlaceListener implements ObserverInterface
{
    protected $optionCollectionFactory;
    protected $processor;

    public function __construct(CollectionFactory $optionCollectionFactory, AddPreviewPieceToResult $processor)
    {
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->processor = $processor;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        $items = $order->getItems();
        foreach ($items as $item) {
            if (!isset($options['options'])) {
                continue;
            }

            $options = $item->getData('product_options');
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
                $dbOption = $dbOptions[$option['option_id']];
                $option['bracelet_piece'] = $dbOption->hasData('piece') ? $dbOption->getData('piece') : 'none';
            }
            $item->setData('product_options', $options);
        }
    }
}
