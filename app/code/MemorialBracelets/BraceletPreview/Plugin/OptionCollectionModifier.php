<?php

namespace MemorialBracelets\BraceletPreview\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use MemorialBracelets\BraceletPreview\Helper\AddPreviewPieceToResult;

class OptionCollectionModifier
{
    /** @var AddPreviewPieceToResult  */
    protected $processor;

    public function __construct(AddPreviewPieceToResult $processor)
    {
        $this->processor = $processor;
    }

    public function afterGetOptions(Collection $subject, $result)
    {
        return $this->processor->addPieceToResult($subject);
    }

    public function afterGetOptionsCollection(Collection $subject, $result)
    {
        return $this->processor->addPieceToResult($subject);
    }
}
