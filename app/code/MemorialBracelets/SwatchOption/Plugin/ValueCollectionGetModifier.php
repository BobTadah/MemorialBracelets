<?php

namespace MemorialBracelets\SwatchOption\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\Store\Model\Store;
use MemorialBracelets\SwatchOption\Helper\AddSwatchDataToResult;

class ValueCollectionGetModifier
{
    protected $swatchProcessor;

    public function __construct(AddSwatchDataToResult $processor)
    {
        $this->swatchProcessor = $processor;
    }

    public function aroundGetValues(Collection $subject, callable $proceed, $storeId)
    {
        $result = $proceed($storeId);

        $this->swatchProcessor->addSwatchesToResult($subject, $storeId);

        return $result;
    }
}
