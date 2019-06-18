<?php

namespace MemorialBracelets\EngravingDisplay\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\DataType\Number;

class EngravingOption extends AbstractModifier
{
    const FIELD_NUMBER_LINES_NAME = 'number_lines';
    const FIELD_SUPPORTIVE_MESSAGE_PRICE_NAME = 'supportive_message_price';
    const FIELD_NAME_ENGRAVING_PRICE_NAME = 'name_engraving_price';

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

        $meta = $this->addNewFields($meta);

        $meta
        [CustomOptions::GROUP_CUSTOM_OPTIONS_NAME]['children']
        [CustomOptions::GRID_OPTIONS_NAME]['children']
        ['record']['children']
        [CustomOptions::CONTAINER_OPTION]['children']
        [CustomOptions::CONTAINER_COMMON_NAME]['children']
        [CustomOptions::FIELD_TYPE_NAME]['arguments']['data']['config']['groupsConfig']
        ['engravable'] = [
            'values' => ['engraving'],
            'indexes' => [
                CustomOptions::CONTAINER_TYPE_STATIC_NAME,
                CustomOptions::FIELD_PRICE_NAME,
                CustomOptions::FIELD_PRICE_TYPE_NAME,
                CustomOptions::FIELD_MAX_CHARACTERS_NAME,
                static::FIELD_NUMBER_LINES_NAME,
                static::FIELD_SUPPORTIVE_MESSAGE_PRICE_NAME,
                static::FIELD_NAME_ENGRAVING_PRICE_NAME,
            ]
        ];

        return $meta;
    }

    public function addNewFields(array $meta)
    {
        $children = &$meta
        [CustomOptions::GROUP_CUSTOM_OPTIONS_NAME]['children']
        [CustomOptions::GRID_OPTIONS_NAME]['children']
        ['record']['children']
        [CustomOptions::CONTAINER_OPTION]['children']
        [CustomOptions::CONTAINER_TYPE_STATIC_NAME]['children'];

        $children[static::FIELD_NUMBER_LINES_NAME] = $this->getNumberLinesFieldConfig(80);
        $children[static::FIELD_SUPPORTIVE_MESSAGE_PRICE_NAME] = $this->getSupportiveMessagePriceConfig(90);
        $children[static::FIELD_NAME_ENGRAVING_PRICE_NAME] = $this->getNameEngravingPriceConfig(100);

        return $meta;
    }

    /**
     * Get config for "Name Engraving Price" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getNameEngravingPriceConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Name Engraving Price '),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_NAME_ENGRAVING_PRICE_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Supportive Message Price" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getSupportiveMessagePriceConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Supportive Message Price '),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SUPPORTIVE_MESSAGE_PRICE_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Number Lines" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getNumberLinesFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Number of Lines '),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_NUMBER_LINES_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }
}
