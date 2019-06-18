<?php

namespace MemorialBracelets\NameProduct\Block\Product\Name\AssociatedProducts;

use Magento\Backend\Block\Template;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;

class ListAssociatedProducts extends Template
{
    protected $_registry;
    protected $priceCurrency;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        PriceCurrencyInterface $currency,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->priceCurrency = $currency;
        $this->_registry = $registry;
    }

    public function getConfiguredProducts()
    {
        $product = $this->_registry('current_product');
        $associatedProducts = $product->getTypeInstance()->getConfiguredProducts($product);
        $products = [];

        foreach ($associatedProducts as $product) {
            $products[] = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'price' => $this->priceCurrency->format($product->getPrice(), false),
                'qty' => $product->getQty(),
                'position' => $product->getPosition()
            ];
        }

        return $products;
    }
}
