<?php

namespace MemorialBracelets\BraceletPreview\Model;

class PreviewOptions
{
    const PART_NONE = 'none';
    const PART_MATERIAL_COLOR = 'material_color';
    const PART_PARACORD = 'paracord_color';
    const PART_TOP_ICON = 'icon_top';
    const PART_LEFT_ICON = 'icon_left';
    const PART_RIGHT_ICON = 'icon_right';
    const PART_LEFT_CHARM = 'charm_left';
    const PART_RIGHT_CHARM = 'charm_right';
    const PART_ENGRAVING = 'engraving';
    const PART_ENGRAVING_STYLE = 'engraving_style';
    const PART_MATERIAL = 'material';
    const PART_SIZE = 'size';
    const PART_CHARM = 'charm';
    const PART_THREAD_COLOR = 'text_color';

    public function getOptions()
    {
        return [
            ['value' => static::PART_NONE, 'label' => __('Not part of Preview')],
            ['value' => static::PART_MATERIAL_COLOR, 'label' => __('Material Color')],
            ['value' => static::PART_THREAD_COLOR, 'label' => __('Thread Color')],
            ['value' => static::PART_PARACORD, 'label' => __('Paracord Color')],
            ['value' => static::PART_TOP_ICON, 'label' => __('Top Icon')],
            ['value' => static::PART_LEFT_ICON, 'label' => __('Left Icon')],
            ['value' => static::PART_RIGHT_ICON, 'label' => __('Right Icon')],
            ['value' => static::PART_LEFT_CHARM, 'label' => __('Left Charm')],
            ['value' => static::PART_RIGHT_CHARM, 'label' => __('Right Charm')],
            ['value' => static::PART_ENGRAVING, 'label' => __('Engraving Text')],
            ['value' => static::PART_ENGRAVING_STYLE, 'label' => __('Engraving Style')],
            ['value' => static::PART_SIZE, 'label' => __('Size')],
            ['value' => static::PART_CHARM, 'label' => __('Charm')],
            //['value' => static::PART_MATERIAL, 'label' => __('Material')],
        ];
    }
}
