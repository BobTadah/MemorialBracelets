<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product options text type block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace MemorialBracelets\EngravingDisplay\Block\Product\View\Options\Type;

use Magento\Catalog\Pricing\Price\BasePrice;

/**
 * Class Engraving
 * @package MemorialBracelets\EngravingDisplay\Block\Product\View\Options\Type
 */
class Engraving extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Product object
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Product option object
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_option;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;

    /**
     * @var \MemorialBracelets\EngravingDisplay\Helper\Data
     */
    protected $engravingHelper;

    /**
     * Engraving constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data           $pricingHelper
     * @param \Magento\Catalog\Helper\Data                     $catalogData
     * @param \MemorialBracelets\EngravingDisplay\Helper\Data  $engravingHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \MemorialBracelets\EngravingDisplay\Helper\Data $engravingHelper,
        array $data = []
    ) {
        $this->engravingHelper = $engravingHelper;
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    /**
     * Returns default value to show in text input
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
    }

    /**
     * @return float
     */
    public function getCustomPrice()
    {
        return floatval($this->calculatePrice('price'));
    }

    /**
     * @return float
     */
    public function getSupportivePrice()
    {
        return floatval($this->calculatePrice('supportive_message_price'));
    }

    /**
     * @return float
     */
    public function getNamePrice()
    {
        return floatval($this->calculatePrice('name_engraving_price'));
    }

    /**
     * @param $key
     * @return float|mixed
     */
    private function calculatePrice($key)
    {
        $price = $this->getOption()->getData($key);
        $type  = $this->getOption()->getPriceType();

        if ($type == 'percent') {
            $basePrice = $this->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();

            return $basePrice * ($price / 100);
        }

        return $price;
    }

    /**
     * @param $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->_formatPrice(['pricing_value' => $price], false);
    }

    /**
     * @return \MemorialBracelets\EngravingDisplay\Helper\Data
     */
    public function getEngravingHelper()
    {
        return $this->engravingHelper;
    }

    /**
     * this will return the class name that will be used for applying the engraving lines font style.
     * @param null $prod
     * @return string
     */
    public function getFontClass($prod = null)
    {
        $class = 'default';

        if ($prod) {
            $this->_product = $prod;
        }

        if ($product = $this->_product) {
            $name = strtolower($this->_product->getName());
            $type = strtolower($this->getOptionText($product));

            if (strpos($type, 'laser engraved black letters') !== false) {
                $class = 'ariel-black';
            } elseif (strpos($name, 'aluminum') !== false && strpos($type, 'recessed') !== false) {
                // case 1: recessed aluminium bracelets
                $class = 'times-roman';
            } elseif (strpos($name, 'steel') !== false && strpos($type, 'recessed') !== false) {
                // case 2: laser engraved black lettering on steel bracelets
                $class = 'century';
            } elseif (strpos($name, 'black lettering') !== false && strpos($type, 'black engraving') !== false) {
                // case 3: laser engraved black lettering on steel bracelets
                $class = 'ariel';
            } elseif (strpos($name, 'leather') !== false && strpos($type, 'laser engraved') !== false) {
                // case 4: laser engraved black lettering on leather bracelets
                $class = 'ariel';
            }
        }

        return $class;
    }

    /**
     * this will attempt to return the product arguments engraving style custom option text value.
     * @param $product
     * @return string
     */
    protected function getOptionText($product)
    {
        $optionText = '';

        foreach ($product->getOptions() as $option) { // cycle product options to find a match
            /** @var \MemorialBracelets\ExtensibleCustomOption\Model\Product\Option $option */
            if ($option->getTitle() == 'Engraving Style') {
                if ($value = $option->getValues()) {
                    // value will be an array with one item.
                    $value      = array_shift($value);
                    $optionText = $value->getTitle();
                    // we found our value: exit loop
                    break;
                }
            }
        }

        return $optionText;
    }

    /**
     * this will return the engraving message stored in the admin config.
     * @return mixed
     */
    public function getEngravingMessage()
    {
        return $this->getEngravingHelper()->getEngravingMessage();
    }

    /**
     * this will return the vietnam engraving message stored in the admin config.
     * @return mixed
     */
    public function getVietnamEngravingMessage()
    {
        return $this->getEngravingHelper()->getVietnamEngravingMessage();
    }
}
