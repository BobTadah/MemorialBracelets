<?php
namespace Aheadworks\Giftcard\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Directory\Helper\Data;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Container;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Amounts
 */
class Amounts extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * Amounts constructor.
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ArrayManager $arrayManager
     * @param Data $directoryHelper
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager,
        Data $directoryHelper
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
        $this->directoryHelper = $directoryHelper;
    }

    public function modifyMeta(array $meta)
    {
        if (!$this->getGroupCodeByField($meta, static::CONTAINER_PREFIX . TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS)) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            static::CONTAINER_PREFIX . TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS,
            $meta
        );

        $meta = $this->arrayManager->set(
            $containerPath . '/children/' . TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'dynamicRows',
                            'label' => __('Amounts'),
                            'required' => 1,
                            'renderDefaultRecord' => false,
                            'recordTemplate' => 'record',
                            'dataScope' => '',
                            'dndConfig' => [
                                'enabled' => false,
                            ],
                            'disabled' => false,
                        ],
                    ],
                ],
                'children' => [
                    'record' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Container::NAME,
                                    'isTemplate' => true,
                                    'is_collection' => true,
                                    'component' => 'Magento_Ui/js/dynamic-rows/record',
                                    'dataScope' => '',
                                ],
                            ],
                        ],
                        'children' => [
                            'website_id' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => Text::NAME,
                                            'formElement' => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataScope' => 'website_id',
                                            'label' => __('Website'),
                                            'options' => $this->getWebsites(),
                                            'value' => $this->getDefaultWebsite(),
                                        ],
                                    ],
                                ],
                            ],
                            'price' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => Field::NAME,
                                            'formElement' => Input::NAME,
                                            'dataType' => Price::NAME,
                                            'label' => __('Amount'),
                                            'validation' => [
                                                'validate-zero-or-greater' => true,
                                                'validate-no-empty' => true,
                                            ],
                                            'enableLabel' => true,
                                            'dataScope' => 'price',
                                        ],
                                    ],
                                ],
                            ],
                            'actionDelete' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => 'actionDelete',
                                            'dataType' => Text::NAME,
                                            'label' => __('Action'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $allowOpenAmountPath = $this->arrayManager->findPath(
            TypeGiftCard::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT,
            $meta,
            null,
            'children'
        );

        if ($allowOpenAmountPath) {
            $meta = $this->arrayManager->merge(
                $allowOpenAmountPath . static::META_CONFIG_PATH,
                $meta,
                [
                    "switcherConfig" => [
                        "enabled" => true,
                        "rules" => [
                            [
                                "value" => "0",
                                "actions" => [
                                    [
                                        "target" => 'product_form.product_form.aw-giftcard-info.container_aw_gc_open_amount_min.aw_gc_open_amount_min',
                                        "callback" => 'hide'
                                    ],
                                    [
                                        "target" => 'product_form.product_form.aw-giftcard-info.container_aw_gc_open_amount_max.aw_gc_open_amount_max',
                                        "callback" => 'hide'
                                    ]
                                ]
                            ],
                            [
                                "value" => "1",
                                "actions" => [
                                    [
                                        "target" => 'product_form.product_form.aw-giftcard-info.container_aw_gc_open_amount_min.aw_gc_open_amount_min',
                                        "callback" => 'show'
                                    ],
                                    [
                                        "target" => 'product_form.product_form.aw-giftcard-info.container_aw_gc_open_amount_max.aw_gc_open_amount_max',
                                        "callback" => 'show'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }

        return $meta;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Get websites list
     *
     * @return array
     */
    protected function getWebsites()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $product = $this->locator->getProduct();

        $websitesList = $this->storeManager->getWebsites();
        $productWebsiteIds = $product->getWebsiteIds();
        foreach ($websitesList as $website) {
            /** @var \Magento\Store\Model\Website $website */
            if (!in_array($website->getId(), $productWebsiteIds)) {
                continue;
            }
            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        }

        return $websites;
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    public function getDefaultWebsite()
    {
        return $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
    }

    /**
     * Retrieve store
     *
     * @return \Magento\Store\Model\Store
     */
    protected function getStore()
    {
        return $this->locator->getStore();
    }
}
