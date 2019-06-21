<?php
namespace Aheadworks\Giftcard\Model\Source\Giftcard;

class IsUsed implements \Magento\Framework\Option\ArrayInterface
{
    const YES_VALUE         = 1;
    const PARTIALLY_VALUE   = 2;
    const NO_VALUE          = 3;

    const YES_LABEL         = 'Yes';
    const PARTIALLY_LABEL   = 'Partially';
    const NO_LABEL          = 'No';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::YES_VALUE,
                'label' => __(self::YES_LABEL)
            ],
            [
                'value' => self::PARTIALLY_VALUE,
                'label' => __(self::PARTIALLY_LABEL)
            ],
            [
                'value' => self::NO_VALUE,
                'label' => __(self::NO_LABEL)
            ]
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array(
            self::YES_VALUE         => __(self::YES_LABEL),
            self::PARTIALLY_VALUE   => __(self::PARTIALLY_LABEL),
            self::NO_VALUE          => __(self::NO_LABEL),
        );
    }

    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
