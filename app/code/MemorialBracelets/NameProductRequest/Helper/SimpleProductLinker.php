<?php

namespace MemorialBracelets\NameProductRequest\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use MemorialBracelets\NameProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Name;

class SimpleProductLinker
{
    const ORDER_PRODUCT_OPTIONS = [
        'stainless-steel cuff bracelet with black letters' => 1,
        'stainless-steel cuff bracelet with recessed letters' => 2,
        'tan leather bracelet with black letters' => 3,
        'colored aluminum cuff bracelet with recessed letters' => 4,
        'black leather bracelet with stainless-steel overlay with black letters' => 5,
        'black leather bracelet with colored aluminum overlay with recessed letters' => 6,
        'stainless-steel dog tag with black letters' => 7,
        'colored aluminum dog tag with recessed letters' => 8,
        'colored aluminum paracord bracelet with recessed letters' => 9
    ];

    /** @var ProductLinkInterfaceFactory */
    private $productLinkFactory;

    /** @var Config */
    private $config;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductLinkInterfaceFactory $productLinkFactory
     * @param Config $config
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductLinkInterfaceFactory $productLinkFactory,
        Config $config,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productLinkFactory = $productLinkFactory;
        $this->config = $config;
        $this->productRepository = $productRepository;
    }

    /**
     * Attach all simple products (that should be) to the name product via product links
     * @param ProductInterface $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function link(ProductInterface $product)
    {
        $links = [];

        foreach ($this->config->getProductLinks() as $linkProduct) {
            //Get the product by sku so we can grab the name to use it to find position in our array.
            $optionProduct = $this->productRepository->get($linkProduct);
            $pName = trim(strtolower($optionProduct->getName()));
            $linkPosition = array_key_exists($pName, self::ORDER_PRODUCT_OPTIONS) ? self::ORDER_PRODUCT_OPTIONS[$pName] : null;

            $link = $this->productLinkFactory->create()
                ->setSku($product->getSku())
                ->setLinkedProductSku($linkProduct)
                ->setLinkedProductType('simple')
                ->setLinkType(Name::TYPE_NAME)
                ->setPosition($linkPosition);

            $links[] = $link;
        }

        if (method_exists($product, 'setData')) {
            $product->setData('ignore_links_flag', false);
        }
        $product->setProductLinks($links);
    }
}
