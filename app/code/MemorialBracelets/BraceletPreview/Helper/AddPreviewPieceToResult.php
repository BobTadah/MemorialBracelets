<?php

namespace MemorialBracelets\BraceletPreview\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;

class AddPreviewPieceToResult
{
    public function addPieceToResult(Collection $subject)
    {
        $table = $subject->getTable('mb_preview_product_option_preview_piece');

        $subject->getSelect()
            ->joinLeft(
                ['default_piece_table' => $table],
                'default_piece_table.option_id = main_table.option_id',
                ['piece' => 'default_piece_table.piece']
            );

        return $subject;
    }
}
