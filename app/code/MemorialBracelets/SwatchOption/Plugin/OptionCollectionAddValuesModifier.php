<?php

namespace MemorialBracelets\SwatchOption\Plugin;

use MemorialBracelets\ExtensibleCustomOption\Model\ResourceModel\Product\Option\Collection;
use MemorialBracelets\SwatchOption\Helper\AddSwatchDataToResult;

class OptionCollectionAddValuesModifier
{
    protected $processor;

    public function __construct(AddSwatchDataToResult $processor)
    {
        $this->processor = $processor;
    }

    public function aroundGetValuesCollection(Collection $subject, callable $proceed, $storeId, $optionIds)
    {
        $result = $proceed($storeId, $optionIds);

        return $this->processor->addSwatchesToResult($result, $storeId);
    }
}
