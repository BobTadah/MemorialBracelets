<?php

namespace MemorialBracelets\NameProduct\CustomerData;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Msrp\Helper\Data as MsrpHelper;

class NameItem extends DefaultItem
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /**
     * NameItem constructor.
     * @param Image                $imageHelper
     * @param MsrpHelper           $msrpHelper
     * @param UrlInterface         $urlBuilder
     * @param ConfigurationPool    $configurationPool
     * @param CheckoutHelper       $checkoutHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Image $imageHelper,
        MsrpHelper $msrpHelper,
        UrlInterface $urlBuilder,
        ConfigurationPool $configurationPool,
        CheckoutHelper $checkoutHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($imageHelper, $msrpHelper, $urlBuilder, $configurationPool, $checkoutHelper);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     *
     * TODO Use Grouped Product Thumbnail depending on settings?  Maybe not, if we're doing thumbnails custom
     * based off the render.
     *
     * {@see Magento\GroupedProduct\CustomerData\GroupedItem.getProductForThumbnail}
     */
    protected function getProductForThumbnail()
    {
        return $this->getProduct();
    }
}
