<?php

namespace MemorialBracelets\ExtensibleCustomOption\Plugin;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard;
use Magento\Sales\Model\Service\OrderService;
use MemorialBracelets\ExtensibleCustomOption\Helper\ExportData;

class PlacePlugin
{
    /**
     * @var ExportData
     */
    private $exportData;

    /**
     * PlacePlugin constructor.
     */
    public function __construct(
        ExportData $exportData
    ) {
        $this->exportData = $exportData;
    }

    /**
     * Iterates over all the order items and assign new item options.
     * @param OrderService $subject
     * @param $order \Magento\Sales\Api\Data\OrderInterface
     * @return array
     */
    public function beforePlace(OrderService $subject, $order)
    {
        /** @var  $item \Magento\Sales\Model\Order\Item */
        foreach ($order->getItems() as $item) {
            //Avoid checking Giftcard products.
            if ($item->getProductType() != Giftcard::TYPE_CODE) {
                //Current item options.
                $itemOptions = $item->getProductOptions();

                //Get data for new item options.
                $engravingUsedLinesQty = $this->exportData->getEngravingUsedLinesQty($itemOptions);
                $engravingType = $this->exportData->getEngravingType($itemOptions);

                //Prepare new item options.
                $additionalOptions = [];
                $additionalOptions[] = [
                    'label' => 'engraving_used_qty',
                    'option_label' => 'engraving_used_qty',
                    'value' => $engravingUsedLinesQty
                ];
                $additionalOptions[] = [
                    'label' => 'engraving_type',
                    'option_label' => 'engraving_type',
                    'value' => $engravingType
                ];

                //Add new item options.
                foreach ($additionalOptions as $additionalOption) {
                    $itemOptions['additional_options'][] = $additionalOption;
                }

                //Save new item options to the item.
                $item->setProductOptions($itemOptions);
            }
        }
        return [$order];
    }
}
