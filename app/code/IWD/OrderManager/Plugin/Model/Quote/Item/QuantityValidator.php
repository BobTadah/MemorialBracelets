<?php

namespace IWD\OrderManager\Plugin\Model\Quote\Item;

use Magento\Framework\Registry;

/**
 * Class QuantityValidator
 * @package IWD\OrderManager\Plugin\Model\Quote\Item
 * @see \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator
 */
class QuantityValidator
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * QuantityValidator constructor.
     * @param Registry $coreRegistry
     */
    public function __construct(Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param $observer
     */
    public function aroundValidate($subject, callable $proceed, $observer)
    {
        if ($this->coreRegistry->registry('validation_disabled') != true) {
            $proceed($observer);
        }
    }
}
