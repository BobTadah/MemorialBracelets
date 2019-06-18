<?php

namespace MemorialBracelets\BraceletPreview\Plugin;

use Magento\Catalog\Model\Product\Option;
use MemorialBracelets\BraceletPreview\Helper\AddPreviewPieceToResult;

class OptionModelModifier
{
    /** @var AddPreviewPieceToResult */
    protected $processor;

    public function __construct(AddPreviewPieceToResult $processor)
    {
        $this->processor = $processor;
    }

    public function afterGetProductOptionCollection(Option $subject, $result)
    {
        return $this->processor->addPieceToResult($result);
    }
}
