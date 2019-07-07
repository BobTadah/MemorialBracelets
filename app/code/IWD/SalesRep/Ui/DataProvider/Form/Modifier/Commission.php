<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\SalesRep\Ui\DataProvider\Form\Modifier;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use IWD\SalesRep\Model\Customer as AttachedCustomer;

class Commission extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    const TIERED_OPTIONS_INFO = 'container_tiered_options_info';
    const TIERED_OPTIONS_GRID = 'container_tiered_options_grid';

    const REPLACE_FIELD = 'commission_rate';

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;



    private $defaultMeta = [
        'general' => [
            'children' => [
                self::REPLACE_FIELD => []
            ]
        ]
    ];

    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        \Magento\Directory\Model\Currency $currency
    ) {
        $this->locator      = $locator;
        $this->currency     = $currency;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Modify produt data for form.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {

        if(empty($data[AttachedCustomer::COMMISSION_TIERED_OPTIONS])) {
            return $data;
        }
        $gridData = $this->getGridData($data);

        return array_replace_recursive(
            $data,
            [
                self::TIERED_OPTIONS_GRID => $gridData,
            ]
        );
    }

    /**
     * Get data for grid.
     *
     * @param array $data
     * @return array
     */
    public function getGridData($data)
    {
        $optionsData = [];
        if (!empty($data[AttachedCustomer::COMMISSION_TIERED_OPTIONS])) {
            $optionsData = $this->unserialize($data[AttachedCustomer::COMMISSION_TIERED_OPTIONS]);
        }

        /**
         * Check defaults -- distill values among interval nulls.
         */
        foreach ($optionsData as $k => $optData) {
            foreach (['order_total', 'commission_rate'] as $priceField) {
                if (isset($optData[$priceField])) {
                    $optionsData[$k][$priceField] = $this->coercePrecision(
                        $optionsData[$k][$priceField]
                    );
                }
            }
        }

        return $optionsData;
    }

    /**
     * Backward Compatibility
     * @param $data
     * @return string
     */
    private function unserialize($data)
    {
        if (class_exists(\Magento\Framework\Serialize\SerializerInterface::class)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
            return $serializer->unserialize($data);
        }
        return \unserialize($data);
    }

    /**
     * Modify product form for subscription management. Assumes subscription attributes are assigned to attr set.
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if(empty($meta)) {
            $meta = $this->defaultMeta;
        }

        $attributePath = $this->arrayManager->findPath(
            self::REPLACE_FIELD,
            $meta,
            null,
            'children'
        );

        if ($attributePath === null) {
            return $meta;
        }
        $containerPath = substr(
            $attributePath,
            0,
            strrpos($attributePath, ArrayManager::DEFAULT_PATH_DELIMITER)
        );
        $container = $this->arrayManager->get($containerPath, $meta);
        $customField = [
            self::TIERED_OPTIONS_INFO => $this->getOptionsInfo(),
            self::TIERED_OPTIONS_GRID => $this->getOptionsGrid(),
            self::TIERED_OPTIONS_INFO . '_hint' => $this->getOptionsHint(),
        ];
        $container += $customField;

        $meta = $this->arrayManager->replace(
            $containerPath,
            $meta,
            $container
        );

        /**
         * Remove original field to avoid confusion
         */
        $meta = $this->removeField($meta);

        return $meta;
    }

    /**
     * Get the intervals input grid definition.
     *
     * @param int $sortOrder
     * @return array
     */
    public function getOptionsGrid($sortOrder = 1005)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Option'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => 'is_delete',
                        'deleteValue' => true,
                        'defaultRecord' => false,
                        'sortOrder' => $sortOrder,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => $this->getIntervalColumns(),
                ],
            ],
        ];
    }

    /**
     * Get intervals grid documentation blurb (explain fields/usage).
     *
     * @param int $sortOrder
     * @return array
     */
    public function getOptionsInfo($sortOrder = 1000)
    {
        $content[] = __('Tiered commission <b>override</b> standard commission settings');
        $content[] = __('If an order is above/less/equal $x then commission rate will be $y<br>');
        $content[] = __('<b style="color: #1979c3">Attention! Options may rewrite previous values.</b>');
        $content[] = __('Example:');
        $content[] = __('<b>Condition & Order Total</b> `> 50` will <b>override</b> previous `> 100` value');
        $content[] = __('<b>Condition & Order Total</b> `< 150` will <b>override</b> previous `> 100` value<br>');

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                    ],
                ],
            ],
            'children' => [
                'options_info' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Tiered Commission Options'),
                                'additionalClasses' => 'news',
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'template' => 'ui/form/components/complex',
                                'content' => implode('<br />', $content),
                                'sortOrder' => $sortOrder,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get intervals grid documentation blurb (explain fields/usage).
     *
     * @param int $sortOrder
     * @return array
     */
    public function getOptionsHint($sortOrder = 1010)
    {
        $hint = '<b style="color: #e22626">*</b> ';
        $content[] = $hint . __('<b>Order Total</b> required, greater than zero');
        $content[] = $hint . __('<b>Commission Rate</b> required, greater than zero');

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                    ],
                ],
            ],
            'children' => [
                'options_info_hint' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => '',
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'template' => 'ui/form/components/complex',
                                'content' => implode('<br />', $content),
                                'sortOrder' => $sortOrder,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the interval columns.
     *
     * @return array
     */
    public function getIntervalColumns()
    {
        return [
            'operand' => $this->getOperand(),
            'order_total' => $this->getTotal(),
            'commission_type' => $this->getCommissionType(),
            'commission_rate' => $this->getCommissionRate(),
            'commission_apply' => $this->getCommissionApply(),
            'actionDelete' => $this->getDeleteAction(),
        ];
    }

    /**
     * Get operand
     *
     * @param int $sortOrder
     * @return array
     */
    public function getOperand($sortOrder = 10)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Condition'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataType' => Text::NAME,
                        'dataScope' => 'operand',
                        'options' => [
                            [
                                'label' => '=',
                                'value' => '=',
                            ],
                            [
                                'label' => '<',
                                'value' => '<',
                            ],
                            [
                                'label' => '>',
                                'value' => '>',
                            ],
                            [
                                'label' => '<=',
                                'value' => '<=',
                            ],
                            [
                                'label' => '>=',
                                'value' => '>=',
                            ],
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get order total
     *
     * @param int $sortOrder
     * @return array
     */
    public function getTotal($sortOrder = 20)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Order Total'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => 'order_total',
                        'dataType' => Price::NAME,
                        'addbefore' => $this->currency->getCurrencySymbol(),
                        'placeholder' => '',
                        'validation' => [
                            'validate-no-empty' => true,
                            'validate-greater-than-zero' => true,
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get commission type options
     *
     * @param int $sortOrder
     * @return array
     */
    public function getCommissionType($sortOrder = 30)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Commission Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataType' => Text::NAME,
                        'dataScope' => 'commission_type',
                        'options' => [
                            [
                                'label' => '% Percent',
                                'value' => 'percent',
                            ],
                            [
                                'label' => 'Fixed',
                                'value' => 'fixed',
                            ],
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get commission rate val
     *
     * @param int $sortOrder
     * @return array
     */
    public function getCommissionRate($sortOrder = 40)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataType' => Price::NAME,
                        'label' => __('Commision Rate'),
                        'enableLabel' => true,
                        'dataScope' => 'commission_rate',
                        'addbefore' => $this->currency->getCurrencySymbol() . ' or %',
                        'placeholder' => '',
                        'validation' => [
                            'validate-no-empty' => true,
                            'validate-greater-than-zero' => true,
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get commission apply options
     *
     * @param int $sortOrder
     * @return array
     */
    public function getCommissionApply($sortOrder = 50)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => _('Apply Commission'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataType' => Text::NAME,
                        'dataScope' => 'commission_apply',
                        'options' => [
                            [
                                'label' => 'Before',
                                'value' => 'before',
                            ],
                            [
                                'label' => 'After',
                                'value' => 'after',
                            ],
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the 'delete' column definition.
     *
     * @param int $sortOrder
     * @return array
     */
    public function getDeleteAction($sortOrder = 60)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'actionDelete',
                        'dataType' => Text::NAME,
                        'label' => ' ',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     *
     * @param array $meta
     * @param string $attr
     * @return array
     */
    public function removeField(array $meta, $attr = self::REPLACE_FIELD)
    {
        $meta = $this->arrayManager->remove(
            $this->arrayManager->findPath(
                $attr,
                $meta,
                null,
                'children'
            ),
            $meta
        );

        return $meta;
    }

    /**
     * Coerce prices to 2 or 4 decimals depending on the precision actually needed.
     *
     * This is to avoid prices always showing as 0.0000 when there's no need.
     *
     * @param int|float $value
     * @return string
     */
    protected function coercePrecision($value)
    {
        $decimal = (float)($value - floor($value));

        if (strlen($decimal) > 4) {
            return sprintf('%0.4f', $value);
        }

        return sprintf('%0.2f', $value);
    }
}