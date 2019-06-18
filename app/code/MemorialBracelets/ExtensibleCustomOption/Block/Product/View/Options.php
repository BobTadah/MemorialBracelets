<?php

namespace MemorialBracelets\ExtensibleCustomOption\Block\Product\View;

use MemorialBracelets\ExtensibleCustomOption\Api\ProductSpecificPriceConfigurationInterface;

class Options extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * Get json representation of
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $config = [];
        foreach ($this->getOptions() as $option) {
            /* @var $option \MemorialBracelets\ExtensibleCustomOption\Model\Product\Option */
            // OVERWRITE: The below line is the customization
            $option->getGroupByType();
            if ($option->hasValues()) {
                $tmpPriceValues = [];
                foreach ($option->getValues() as $valueId => $value) {
                    $tmpPriceValues[$valueId] = $this->_getPriceConfiguration($value);
                }
                $priceValue = $tmpPriceValues;
            } else {
                $priceValue = $this->_getPriceConfiguration($option);
            }
            $config[$option->getId()] = $priceValue;
        }

        $configObj = new \Magento\Framework\DataObject(
            [
                'config' => $config,
            ]
        );

        //pass the return array encapsulated in an object for the other modules to be able to alter it eg: weee
        $this->_eventManager->dispatch('catalog_product_option_price_configuration_after', ['configObj' => $configObj]);

        $config=$configObj->getConfig();

        return $this->_jsonEncoder->encode($config);
    }

    public function _getPriceConfiguration($option)
    {
        if (!($option instanceof ProductSpecificPriceConfigurationInterface)) {
            return parent::_getPriceConfiguration($option);
        }
        $product = $this->_registry->registry('current_product');

        $optionPrice = $this->pricingHelper->currency($option->getPriceBasedOnProduct($product), false, false);
        $data = [
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->pricingHelper->currency($option->getRegularPrice(), false, false),
                    'adjustments' => [],
                ],
                'basePrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $option->getProduct(),
                        $optionPrice,
                        false,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $option->getProduct(),
                        $optionPrice,
                        true,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
            ],
            'type' => $option->getPriceType(),
            'name' => $option->getTitle()
        ];
        return $data;
    }
}
