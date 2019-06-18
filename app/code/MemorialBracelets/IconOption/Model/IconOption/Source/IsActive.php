<?php

namespace MemorialBracelets\IconOption\Model\IconOption\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MemorialBracelets\IconOption\Model\IconOption;

class IsActive implements OptionSourceInterface
{
    /** @var IconOption  */
    protected $icon;

    public function __construct(IconOption $icon)
    {
        $this->icon = $icon;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $availableOptions = $this->icon->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = ['label' => $value, 'value' => $key];
        }
        return $options;
    }
}
