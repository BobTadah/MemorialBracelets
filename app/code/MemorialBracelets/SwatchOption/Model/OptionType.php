<?php

namespace MemorialBracelets\SwatchOption\Model;

use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Model\Product\Option\Type\Select;
use Magento\Framework\Api\SearchCriteriaInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use MemorialBracelets\SizeOption\Api\SizeOptionInterface;
use MemorialBracelets\SizeOption\Api\SizeOptionRepositoryInterface;

/**
 * Class OptionType
 * @package MemorialBracelets\SizeOption\Model
 */
class OptionType extends Select
{
    const SWATCH_TYPE_IMAGE = 'swatch_image';
    const SWATCH_TYPE_COLOR = 'swatch_color';
    const SWATCH_TYPE_ABBR = 'swatch_abbr';

    public static function subTypes()
    {
        return [static::SWATCH_TYPE_ABBR, static::SWATCH_TYPE_COLOR, static::SWATCH_TYPE_IMAGE];
    }
}
