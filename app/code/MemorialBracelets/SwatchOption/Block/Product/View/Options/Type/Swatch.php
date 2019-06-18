<?php

namespace MemorialBracelets\SwatchOption\Block\Product\View\Options\Type;

use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use \Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\View\Element\Template\Context;
use MemorialBracelets\SwatchOption\Helper\ImageStorageConfiguration;

/**
 * Class Size
 * @package MemorialBracelets\SizeOption\Block\Product\View\Options\Type
 */
class Swatch extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /** @var SearchCriteriaBuilderFactory  */
    protected $criteriaFactory;

    /** @var ImageStorageConfiguration  */
    protected $configuration;

    public function __construct(
        Context $context,
        PriceHelper $pricingHelper,
        CatalogHelper $catalogData,
        SearchCriteriaBuilderFactory $criteriaFactory,
        ImageStorageConfiguration $configuration,
        array $data = []
    ) {
        $this->criteriaFactory = $criteriaFactory;
        $this->configuration = $configuration;
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    /**
     * Returns default value to show in picker
     *
     * @return string
     */
    public function getDefaultValue()
    {
        $val = $this->getProduct()->getPreconfiguredValues()->getData('options/'.$this->getOption()->getId());
        if (is_null($val) && $this->getOption()->getIsRequire() && count($this->getOption()->getValues()) == 1) {
            $values = $this->getOption()->getValues();
            $value = reset($values);
            if (!is_null($value)) {
                $val = $value->getOptionTypeId();
            }
        }
        return $val;
    }

    /**
     * @return ImageStorageConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getValues()
    {
        return $this->getOption()->getValues();
    }
}
