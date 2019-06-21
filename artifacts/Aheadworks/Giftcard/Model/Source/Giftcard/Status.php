<?php
namespace Aheadworks\Giftcard\Model\Source\Giftcard;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const AVAILABLE_VALUE       = 1;
    const EXPIRED_VALUE         = 2;
    const USED_VALUE            = 3;
    const DEACTIVATED_VALUE     = 4;

    const AVAILABLE_LABEL       = 'Active';
    const EXPIRED_LABEL         = 'Expired';
    const USED_LABEL            = 'Used';
    const DEACTIVATED_LABEL     = 'Deactivated';

    const EXPIRED_ERROR_MESSAGE  = 'This card has expired';
    const USED_ERROR_MESSAGE     = 'The balance on this card is 0';
    const DEACTIVATED_ERROR_MESSAGE = 'This card has been deactivated';
    const DEFAULT_ERROR_MESSAGE  = 'Gift Card Code is not valid.';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AVAILABLE_VALUE,
                'label' => __(self::AVAILABLE_LABEL)
            ],
            [
                'value' => self::EXPIRED_VALUE,
                'label' => __(self::EXPIRED_LABEL)
            ],
            [
                'value' => self::USED_VALUE,
                'label' => __(self::USED_LABEL)
            ],
            [
                'value' => self::DEACTIVATED_VALUE,
                'label' => __(self::DEACTIVATED_LABEL)
            ]
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array(
            self::AVAILABLE_VALUE       => __(self::AVAILABLE_LABEL),
            self::EXPIRED_VALUE         => __(self::EXPIRED_LABEL),
            self::USED_VALUE            => __(self::USED_LABEL),
            self::DEACTIVATED_VALUE     => __(self::DEACTIVATED_LABEL)
        );
    }

    /**
     * @param int $value
     * @return null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }

    /**
     * Retrieves error message by state value
     *
     * @param int $value
     * @return string
     */
    public function getErrorMessage($value)
    {
        $map = [
            self::EXPIRED_VALUE     => self::EXPIRED_ERROR_MESSAGE,
            self::USED_VALUE        => self::USED_ERROR_MESSAGE,
            self::DEACTIVATED_VALUE    => self::DEACTIVATED_ERROR_MESSAGE
        ];
        return isset($map[$value]) ? $map[$value] : self::DEFAULT_ERROR_MESSAGE;
    }
}
