<?php
namespace Aheadworks\Giftcard\Model\Source\History;

class Actions implements \Magento\Framework\Option\ArrayInterface
{
    const CREATED_VALUE         = 1;
    const UPDATED_VALUE         = 2;
    const USED_VALUE            = 3;
    const PARTIALLY_USED_VALUE  = 4;
    const EXPIRED_VALUE         = 5;
    const DEACTIVATED_VALUE     = 6;

    const CREATED_LABEL         = 'Created';
    const UPDATED_LABEL         = 'Updated';
    const USED_LABEL            = 'Used';
    const PARTIALLY_USED_LABEL  = 'Partially Used';
    const EXPIRED_LABEL         = 'Expired';
    const DEACTIVATED_LABEL        = 'Deactivated';

    const BY_ADMIN_MESSAGE_VALUE            = 0;
    const BY_ORDER_MESSAGE_VALUE            = 1;
    const BY_CREDITMEMO_MESSAGE_VALUE       = 2;
    const BY_REDEEM_TO_STORECREDIT_VALUE    = 3;

    const BY_ADMIN_MESSAGE_LABEL            = 'By admin: %1.';
    const BY_ORDER_MESSAGE_LABEL            = 'Order #%1.';
    const BY_CREDITMEMO_MESSAGE_LABEL       = 'Refund for order #%1.';

    public function toOptionArray()
    {
        return array(
            self::CREATED_VALUE => __(self::CREATED_LABEL),
            self::UPDATED_VALUE => __(self::UPDATED_LABEL),
            self::USED_VALUE => __(self::USED_LABEL),
            self::PARTIALLY_USED_VALUE => __(self::PARTIALLY_USED_LABEL),
            self::EXPIRED_VALUE => __(self::EXPIRED_LABEL),
            self::DEACTIVATED_VALUE => __(self::DEACTIVATED_LABEL)
        );
    }

    public function getMessageLabelByType($messageType)
    {
        $label = '';
        switch ($messageType) {
            case self::BY_ADMIN_MESSAGE_VALUE :
                $label = self::BY_ADMIN_MESSAGE_LABEL;
                break;
            case self::BY_ORDER_MESSAGE_VALUE :
                $label = self::BY_ORDER_MESSAGE_LABEL;
                break;
            case self::BY_CREDITMEMO_MESSAGE_VALUE :
                $label = self::BY_CREDITMEMO_MESSAGE_LABEL;
                break;
        }
        return $label;
    }
}
