<?php

namespace MemorialBracelets\SwatchOption\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\Store\Model\Store;

class AddSwatchDataToResult
{
    public function addSwatchesToResult(Collection $subject, $storeId)
    {
        $connection = $subject->getConnection();
        $optionSwatchTable = $subject->getTable('mb_swatch_product_option_type_swatch');

        /*
         * These expressions are basically, IF the first parameter is true, use the second, otherwise use the third
         *
         * So we utilize them to allow a store value to replace the default value.
         *
         * Neat!
         */

        $abbrExpr = $connection->getCheckSql(
            'store_value_swatch.swatch_abbr IS NULL',
            'default_value_swatch.swatch_abbr',
            'store_value_swatch.swatch_abbr'
        );

        $colorExpr = $connection->getCheckSql(
            'store_value_swatch.swatch_color IS NULL',
            'default_value_swatch.swatch_color',
            'store_value_swatch.swatch_color'
        );

        $imageExpr = $connection->getCheckSql(
            'store_value_swatch.image IS NULL',
            'default_value_swatch.image',
            'store_value_swatch.image'
        );

        $joinExprDefault = 'default_value_swatch.option_type_id = main_table.option_type_id AND '.
            $connection->quoteInto('default_value_swatch.store_id = ?', Store::DEFAULT_STORE_ID);

        $joinExprStore = 'store_value_swatch.option_type_id = main_table.option_type_id AND '.
            $connection->quoteInto('store_value_swatch.store_id = ?', $storeId);

        $subject->getSelect()
            ->joinLeft(
                ['default_value_swatch' => $optionSwatchTable],
                $joinExprDefault,
                [
                    'default_swatch_abbr'  => 'swatch_abbr',
                    'default_swatch_color' => 'swatch_color',
                    'default_image'        => 'image',
                ]
            )->joinLeft(
                ['store_value_swatch' => $optionSwatchTable],
                $joinExprStore,
                [
                    'store_swatch_abbr'  => 'swatch_abbr',
                    'store_swatch_color' => 'swatch_color',
                    'store_image'        => 'image',
                    // Now the actual "to-use" values
                    'swatch_abbr'        => $abbrExpr,
                    'swatch_color'       => $colorExpr,
                    'image'              => $imageExpr,
                ]
            );

        return $subject;
    }
}
