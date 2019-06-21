<?php
namespace Aheadworks\Giftcard\Model\Plugin\ResourceModel;

class Order
{
    /**
     * @param \Magento\Sales\Model\ResourceModel\Order $resource
     * @param \Magento\Sales\Model\Order $order
     */
    public function beforeSave($resource, $order)
    {
        if ($order->hasForcedCanCreditmemo()) {
            $order->setForcedCanCreditmemo(false);
            foreach ($order->getAllItems() as $item) {
                if ($item->canRefund()) {
                    $order->setForcedCanCreditmemo(true);
                }
            }
        }
    }
}
