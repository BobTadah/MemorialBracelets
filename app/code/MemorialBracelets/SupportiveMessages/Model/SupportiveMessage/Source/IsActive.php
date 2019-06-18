<?php

namespace MemorialBracelets\SupportiveMessages\Model\SupportiveMessage\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MemorialBracelets\SupportiveMessages\Model\SupportiveMessage;

class IsActive implements OptionSourceInterface
{
    /** @var SupportiveMessage  */
    protected $message;

    public function __construct(SupportiveMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $availableOptions = $this->message->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = ['label' => $value, 'value' => $key];
        }
        return $options;
    }
}
