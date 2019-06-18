<?php

namespace MemorialBracelets\SwatchOption\Plugin;

use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use MemorialBracelets\SwatchOption\Helper\MediaElementArray;
use MemorialBracelets\SwatchOption\Model\OptionType;

class ProductFormModifier extends AbstractModifier
{
    const GRID_TYPE_SWATCH_NAME = 'swatches';
    const FIELD_IMAGE_NAME = 'image';
    const FIELD_TYPE_NAME = 'swatch_type';
    const FIELD_ABBR_NAME = 'swatch_abbr';
    const FIELD_COLOR_NAME = 'swatch_color';
    const FIELD_PRICE_NAME = CustomOptions::FIELD_PRICE_NAME;
    const FIELD_ENABLE = CustomOptions::FIELD_ENABLE;
    const GRID_OPTIONS_NAME = CustomOptions::GRID_OPTIONS_NAME;

    protected $storeManager;
    protected $productOptionsPrice;
    protected $locator;
    protected $arrayManager;
    protected $mediaProcessor;

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductOptionsPrice $productOptionsPrice,
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        MediaElementArray $mediaProcessor
    ) {
        $this->storeManager = $storeManager;
        $this->productOptionsPrice = $productOptionsPrice;
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->mediaProcessor = $mediaProcessor;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $options = [];
        $productOptions = $this->locator->getProduct()->getOptions() ?: [];

        foreach ($productOptions as $index => $option) {
            if ($option->getGroupByType() != 'swatch') {
                continue;
            }
            $options[$index] = $this->formatPriceByPath(static::FIELD_PRICE_NAME, $option->getData());
            $values = $option->getValues();
            if (empty($values)) {
                continue;
            }

            $valuesData = [];
            foreach ($values as $value) {
                $valueData = $value->getData();
                $valueData = $this->formatPriceByPath(
                    static::FIELD_PRICE_NAME,
                    $valueData
                );
                if ($valueData['image']) {
                    $valueData['image'] = $this->mediaProcessor->getArray($valueData['image']);
                }

                $valuesData[] = $valueData;
            }
            $options[$index][static::GRID_TYPE_SWATCH_NAME] = $valuesData;
        }

        $result = array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::FIELD_ENABLE      => 1,
                        static::GRID_OPTIONS_NAME => $options,
                    ],
                ],
            ]
        );

        return $result;
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

        // This large block of array is responsible for adding the swatch container
        // This container will be added to every custom option.  We'll hide it using view/adminhtml/web/catalog-custom-options-type-hook.js

        $meta
        [CustomOptions::GROUP_CUSTOM_OPTIONS_NAME]['children']
        [CustomOptions::GRID_OPTIONS_NAME]['children']
        ['record']['children']
        [CustomOptions::CONTAINER_OPTION]['children']
        [static::GRID_TYPE_SWATCH_NAME] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel'      => __('Add Value'),
                        'componentType'       => DynamicRows::NAME,
                        'component'           => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses'   => 'admin__field-wide',
                        'deleteProperty'      => CustomOptions::FIELD_IS_DELETE,
                        'deleteValue'         => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder'           => 40,
                    ],
                ],
            ],
            'children'  => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType'    => Container::NAME,
                                'component'        => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => CustomOptions::FIELD_SORT_ORDER_NAME,
                                'isTemplate'       => true,
                                'is_collection'    => true,
                            ],
                        ],
                    ],
                    'children'  => [
                        CustomOptions::FIELD_TITLE_NAME      => $this->getTitleFieldConfig(10),
                        CustomOptions::FIELD_PRICE_NAME      => $this->getPriceFieldConfig(20),
                        CustomOptions::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                        static::FIELD_ABBR_NAME              => $this->getAbbrFieldConfig(32),
                        static::FIELD_COLOR_NAME             => $this->getColorFieldConfig(33),
                        static::FIELD_IMAGE_NAME             => $this->getImageFieldConfig(35),
                        CustomOptions::FIELD_SKU_NAME        => $this->getSkuFieldConfig(40),
                        CustomOptions::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(50),
                        CustomOptions::FIELD_IS_DELETE       => $this->getIsDeleteFieldConfig(60),
                    ],
                ],
            ],
        ];

        $meta
        [CustomOptions::GROUP_CUSTOM_OPTIONS_NAME]['children']
        [CustomOptions::GRID_OPTIONS_NAME]['children']
        ['record']['children']
        [CustomOptions::CONTAINER_OPTION]['children']
        [CustomOptions::CONTAINER_COMMON_NAME]['children']
        [CustomOptions::FIELD_TYPE_NAME]['arguments']['data']['config']['groupsConfig']
        ['swatch'] = [
            'values'  => OptionType::subTypes(),
            'indexes' => [
                CustomOptions::CONTAINER_TYPE_STATIC_NAME,
                static::GRID_TYPE_SWATCH_NAME,
            ],
        ];

        return $meta;
    }

    protected function getSwatchDisplayTypeFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Display Type'),
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => 'swatch_display',
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => [
                                ['value' => 'image', 'label' => __('Image')],
                                ['value' => 'abbr', 'label' => __('Abbreviation')],
                                ['value' => 'color', 'label' => __('Color')],
                            ]
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * Get config for "Title" fields
     *
     * @param int   $sortOrder
     * @param array $options
     * @return array
     */
    protected function getTitleFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement'   => Input::NAME,
                            'dataScope'     => CustomOptions::FIELD_TITLE_NAME,
                            'dataType'      => Text::NAME,
                            'sortOrder'     => $sortOrder,
                            'validation'    => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    protected function getColorFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Color'),
                            'componentType' => Field::NAME,
                            'formElement'   => Input::NAME,
                            'dataScope'     => static::FIELD_COLOR_NAME,
                            'dataType'      => Text::NAME,
                            'sortOrder'     => $sortOrder,
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    protected function getAbbrFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Abbreviation'),
                            'componentType' => Field::NAME,
                            'formElement'   => Input::NAME,
                            'dataScope'     => static::FIELD_ABBR_NAME,
                            'dataType'      => Text::NAME,
                            'sortOrder'     => $sortOrder,
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    protected function getImageFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Image'),
                            'componentType' => Field::NAME,
                            'formElement'   => 'fileUploader',
                            'dataScope'     => static::FIELD_IMAGE_NAME,
                            'dataType'      => Media::NAME,
                            'sortOrder'     => $sortOrder,
                            'validation'    => [],
                            'uploaderConfig' => [
                                'url' => 'swatchoption/index/fileUpload'
                            ]
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * Get config for "Price" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getPriceFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Price'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => CustomOptions::FIELD_PRICE_NAME,
                        'dataType'      => Number::NAME,
                        'addbefore'     => $this->getCurrencySymbol(),
                        'sortOrder'     => $sortOrder,
                        'validation'    => [
                            'validate-zero-or-greater' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price Type" field
     *
     * @param int   $sortOrder
     * @param array $config
     * @return array
     */
    protected function getPriceTypeFieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Price Type'),
                            'componentType' => Field::NAME,
                            'formElement'   => Select::NAME,
                            'dataScope'     => CustomOptions::FIELD_PRICE_TYPE_NAME,
                            'dataType'      => Text::NAME,
                            'sortOrder'     => $sortOrder,
                            'options'       => $this->productOptionsPrice->toOptionArray(),
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    /**
     * Get config for "SKU" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getSkuFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('SKU'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => CustomOptions::FIELD_SKU_NAME,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for sorting
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataScope'     => CustomOptions::FIELD_SORT_ORDER_NAME,
                        'dataType'      => Number::NAME,
                        'visible'       => false,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for removing rows
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getIsDeleteFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit'           => true,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    protected function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * Format float number to have two digits after delimiter
     *
     * @param string $path
     * @param array  $data
     * @return array
     */
    protected function formatPriceByPath($path, array $data)
    {
        $value = $this->arrayManager->get($path, $data);

        if (is_numeric($value)) {
            $data = $this->arrayManager->replace($path, $data, $this->formatPrice($value));
        }

        return $data;
    }
}
