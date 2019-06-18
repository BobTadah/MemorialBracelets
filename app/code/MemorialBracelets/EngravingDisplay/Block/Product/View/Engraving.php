<?php

namespace MemorialBracelets\EngravingDisplay\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

/**
 * Class Engraving
 * @package MemorialBracelets\EngravingDisplay\Block\Product\View
 */
class Engraving extends Template
{
    /** @var Registry  */
    protected $coreRegistry;

    /**
     * Engraving constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Engraving Options:
     * [Enabled]
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue('catalog/engraving/enabled', 'store');
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Engraving Options:
     * [Maximum Number of Engraving lines]
     *
     * @return mixed
     */
    public function getEngravingLines()
    {
        return $this->_scopeConfig->getValue('catalog/engraving/lines', 'store');
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Engraving Options:
     * [Maximum Characters Per Custom Text Line]
     *
     * @return mixed
     */
    public function getMaxChars()
    {
        return $this->_scopeConfig->getValue('catalog/engraving/max_char', 'store');
    }

    /**
     * This will return the current product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }
}
