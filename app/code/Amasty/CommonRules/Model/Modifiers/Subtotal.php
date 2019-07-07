<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_CommonRules
 */


namespace Amasty\CommonRules\Model\Modifiers;

class Subtotal implements \Amasty\CommonRules\Model\Modifiers\ModifierInterface
{
    /**
     * @var string
     */
    protected $sectionConfig = '';

    /**
     * @var \Amasty\CommonRules\Model\Config
     */
    private $config;

    /**
     * Subtotal constructor.
     * @param \Amasty\CommonRules\Model\Config $config
     */
    function __construct(\Amasty\CommonRules\Model\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $object
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function modify($object)
    {
        $subtotal = $object->getSubtotal();
        $baseSubtotal = $object->getBaseSubtotal();
        $packageValueWithDiscount = $object->getSubtotal();

        if ($this->config->getTaxIncludeConfig($this->getSectionConfig())) {
            $subtotal += $object->getTaxAmount();
            $baseSubtotal += $object->getBaseTaxAmount();
            $packageValueWithDiscount += $object->getTaxAmount();
        }

        if ($this->config->getUseSubtotalConfig($this->getSectionConfig())) {
            $subtotal += $object->getDiscountAmount();
            $baseSubtotal += $object->getBaseDiscountAmount();
            $packageValueWithDiscount += $object->getDiscountAmount();
        }

        $object->setSubtotal($subtotal);
        $object->setBaseSubtotal($baseSubtotal);
        $object->setPackageValueWithDiscount($packageValueWithDiscount);

        return $object;
    }

    /**
     * @param $sectionConfig
     * @return $this
     */
    public function setSectionConfig($sectionConfig)
    {
        $this->sectionConfig = $sectionConfig;

        return $this;
    }

    /**
     * @return string
     */
    public function getSectionConfig()
    {
        return $this->sectionConfig;
    }
}
