<?php

namespace MemorialBracelets\CharmOption\Model\CharmOption\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MemorialBracelets\CharmOption\Model\CharmOption;

class IsActive implements OptionSourceInterface
{
    /** @var CharmOption  */
    protected $charm;

    public function __construct(CharmOption $charm)
    {
        $this->charm = $charm;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $availableOptions = $this->charm->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = ['label' => $value, 'value' => $key];
        }
        return $options;
    }
}
