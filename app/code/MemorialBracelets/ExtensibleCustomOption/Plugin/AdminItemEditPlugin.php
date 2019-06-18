<?php

namespace MemorialBracelets\ExtensibleCustomOption\Plugin;

use IWD\OrderManager\Model\Order\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use MemorialBracelets\ExtensibleCustomOption\Helper\ExportData;

class AdminItemEditPlugin
{
    /**
     * @var ExportData
     */
    private $exportData;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * PlacePlugin constructor.
     */
    public function __construct(
        ExportData $exportData,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->exportData = $exportData;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Edit engraving_used_qty and engraving_type options if engraving lines have been modified.
     * @param Order $subject
     * @param string[] $params
     * @return array
     */
    public function beforeEditItems(Order $order, $params)
    {
        /** @var  $item \Magento\Sales\Model\Order\Item */
        foreach ($params['item'] as $k => $item) {
            //Current item options. The ones that were modified and passed through params. Not yet saved in the DB.
            $itemOptions = $item['product_options'];

            if (!empty($itemOptions)) {
                try {
                    $itemOptions = unserialize($itemOptions);
                } catch (\Exception $e) {
                    $itemOptions = str_replace("\r\n", "\n", $itemOptions);
                    $itemOptions = unserialize($itemOptions);
                }

                foreach ($itemOptions['options'] as $i => $option) {
                    if ($option['option_type'] == 'engraving') {
                        $optionValue = str_replace('\r\n', "\n", $option['option_value']);
                        $itemOptions['options'][$i]['option_value'] = $optionValue;
                        $engValue = json_decode($option['option_value'])->text;
                        $itemOptions['options'][$i]['value'] = $engValue;
                        $itemOptions['options'][$i]['print_value'] = $engValue;
                    }
                }

                //Get data for new item options.
                $engravingUsedLinesQty = $this->exportData->getEngravingUsedLinesQty($itemOptions);

                //Since $itemOptions from params doesn't contain the name product Id, we should grab it from the order.
                $orderItems = $order->getAllItems();
                foreach ($orderItems as $orderItem) {
                    //Find the current item in the order.
                    if ($orderItem->getItemId() == $k) {
                        // For new items added through admin order edit page, there is no product in info_buyRequest.
                        // So we grab it from the item in that case.
                        $parentProductId = isset($orderItem->getProductOptions()['info_buyRequest']['product'])
                            ? $orderItem->getProductOptions()['info_buyRequest']['product']
                            : $orderItem->getProductId();
                        //Set the parent product id in the $itemOptions.
                        $itemOptions['info_buyRequest']['product'] = $parentProductId;
                        break;
                    }
                }

                //Now with the correct parent product set, determine the engraving type.
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

                //Serialize and set new item options to be saved.
                $item['product_options'] = serialize($itemOptions);
                $params['item'][$k] = $item;
            }
        }

        //Continue process with updated params.
        return [$params];
    }

    /**
     * Check engraving_used_qty and engraving_type options for NEW items from ADMIN order edits.
     * @param Order $subject
     * @param string[] $result
     * @return array
     */
    public function afterEditItems(Order $order, $result)
    {
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $k => $orderItem) {
            //If already has additional_options, then jump to next item.
            if (isset($orderItem->getProductOptions()['additional_options'])) {
                continue;
            }

            //Current item options.
            $itemOptions = $orderItem['product_options'];

            //Get data for new item options.
            $engravingUsedLinesQty = $this->exportData->getEngravingUsedLinesQty($itemOptions);

            $parentProductId = isset($orderItem->getProductOptions()['info_buyRequest']['product'])
                ? $orderItem->getProductOptions()['info_buyRequest']['product']
                : $orderItem->getProductId();
            //Set the parent product id in the $itemOptions.
            $itemOptions['info_buyRequest']['product'] = $parentProductId;

            //Now with the correct parent product set, determine the engraving type.
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
            // Serialize and set new item options to be saved.
            $orderItem['product_options'] = serialize($itemOptions);
            $orderItems[$k] = $orderItem;
        }

        //Update order items.
        $order->setItems($orderItems);
        $this->orderRepository->save($order);

        return $result;
    }
}
