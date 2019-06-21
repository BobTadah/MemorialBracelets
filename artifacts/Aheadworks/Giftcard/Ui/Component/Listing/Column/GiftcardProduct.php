<?php
namespace Aheadworks\Giftcard\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class GiftcardProduct extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Model\ProductFactory $productRepository
     * @param \Magento\Framework\UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\UrlInterface $url,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->productFactory = $productFactory;
        $this->url = $url;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $giftCard = new \Magento\Framework\DataObject($item);
                if ($productId = $giftCard->getProductId()) {
                    $product = $this->productFactory->create()->load($productId);
                    if ($product->getId()) {
                        $item[$fieldName . '_name'] = $product->getName();
                        $item[$fieldName . '_url'] = $this->url->getUrl(
                            'catalog/product/edit',
                            ['id' => $product->getId()]
                        );
                    }
                }
            }
        }

        return $dataSource;
    }
}
