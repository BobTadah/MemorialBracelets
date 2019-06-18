<?php

namespace MemorialBracelets\NameProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

class CustomOptions extends AbstractModifier
{
    const PRODUCT_TYPE_NAME = 'name';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(LocatorInterface $locator, ArrayManager $arrayManager)
    {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        if ($product->getTypeId() === static::PRODUCT_TYPE_NAME) {
            $data = $this->arrayManager->remove(
                $this->arrayManager->findPath(CustomOptionsModifier::FIELD_ENABLE, $data),
                $data
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === static::PRODUCT_TYPE_NAME) {
            $meta = $this->arrayManager->remove(CustomOptionsModifier::GROUP_CUSTOM_OPTIONS_NAME, $meta);
        }

        return $meta;
    }
}
