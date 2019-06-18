<?php

namespace MemorialBracelets\NameProduct\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\ArrayUtils;

class Name extends \MemorialBracelets\NameProduct\Block\Product\View\Type\Name
{
    protected $pricing;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        PricingHelper $pricingHelper,
        array $data
    ) {
        $this->pricing = $pricingHelper;
        parent::__construct($context, $arrayUtils, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_useLinkForAsLowAs = false;
    }

    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        $product = $this->getData('product');
        if ($product->getTypeInstance()->getStoreFilter($product) === null) {
            $product->getTypeInstance()->setStoreFilter(
                $this->_storeManager->getStore($product->getStoreId()),
                $product
            );
        }

        return $product;
    }

    public function getConfiguredProducts()
    {
        $product = $this->getProduct();
        $result = $product->getTypeInstance()->getConfiguredProducts($product);

        $storeId = $product->getStoreId();
        foreach ($result as $item) {
            $item->setStoreId($storeId);
        }

        return $result;
    }

    public function setPreconfiguredValue()
    {
        $configValues = $this->getProduct()->getPreconfiguredValues()->getSuperGroup();
        if (is_array($configValues)) {
            $associatedProducts = $this->getConfiguredProducts();
            foreach ($associatedProducts as $item) {
                if (isset($configValues[$item->getId()])) {
                    $item->setQty($configValues[$item->getId()]);
                }
            }
        }
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCanShowProductPrice($product)
    {
        return true;
    }

    public function getIsLastFieldset()
    {
        $isLast = $this->getData('is_last_fieldset');
        if (!$isLast) {
            $options = $this->getProduct()->getOptions();
            return !$options || !count($options);
        }
        return $isLast;
    }

    public function getCurrencyPrice($price)
    {
        $store = $this->getProduct()->getStore();
        return $this->pricing->currencyByStore($price, $store, false);
    }
}
