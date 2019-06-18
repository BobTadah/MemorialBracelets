<?php

namespace MemorialBracelets\BraceletPreview\Plugin;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use MemorialBracelets\BraceletPreview\Model\PreviewOptions;

class ProductFormModifier extends AbstractModifier
{
    const PREVIEW_PIECE = 'preview_part';

    protected $previewOptions;

    public function __construct(PreviewOptions $previewOptions)
    {
        $this->previewOptions = $previewOptions;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if (!isset($meta[CustomOptions::GROUP_CUSTOM_OPTIONS_NAME])) {
            return $meta;
        }

        $meta
        [CustomOptions::GROUP_CUSTOM_OPTIONS_NAME]['children']
        [CustomOptions::GRID_OPTIONS_NAME]['children']
        ['record']['children']
        [CustomOptions::CONTAINER_OPTION]['children']
        [CustomOptions::CONTAINER_COMMON_NAME]['children']
        [static::PREVIEW_PIECE] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'sortOrder'     => 50,
                        'label'         => __('Bracelet Piece'),
                        'componentType' => Field::NAME,
                        'formElement'   => Select::NAME,
                        'dataScope'     => OptionResourceSaveModifier::DATA_KEY,
                        'dataType'      => Text::NAME,
                        'options'       => $this->previewOptions->getOptions(),
                        'value'         => PreviewOptions::PART_NONE,
                    ],
                ],
            ],
        ];

        return $meta;
    }
}
