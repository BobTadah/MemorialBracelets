<?php

namespace MemorialBracelets\NameProduct\Plugin\Iwd\OrderManager\Model\Order;

use IWD\OrderManager\Model\Order\Converter as IwdConverter;
use Magento\Sales\Api\Data\OrderItemInterface;
use IWD\OrderManager\Model\Order\Item;

class Converter
{
    /** @var Item */
    private $orderItem;

    /**
     * @param Item $orderItem
     */
    public function __construct(\IWD\OrderManager\Model\Order\Item $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * @param IwdConverter $subject
     * @param callable $proceed
     * @param $itemId
     * @param $params
     * @return OrderItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreateNewOrderItem(IwdConverter $subject, callable $proceed, $itemId, $params)
    {
        $oldOrderItem = $this->orderItem->load($itemId);
        /** @var OrderItemInterface $newOrderItem */
        $newOrderItem = $proceed($itemId, $params);

        $superProductConfig = $oldOrderItem->getData('product_options/super_product_config');
        $hasDataKey = !!$superProductConfig && isset($superProductConfig['product_type']);
        if (!$hasDataKey || $superProductConfig['product_type'] != 'name') {
            return $newOrderItem;
        }

        $data = $newOrderItem->getData();
        $data['product_options']['super_product_config'] = $superProductConfig;
        $newOrderItem->setData($data);

        return $newOrderItem;
    }
}
