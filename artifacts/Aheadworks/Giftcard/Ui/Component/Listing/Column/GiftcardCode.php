<?php
namespace Aheadworks\Giftcard\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class GiftcardCode extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
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
                $item[$fieldName . '_url'] = $this->url->getUrl(
                    'aw_giftcard_admin/giftcard/edit',
                    ['id' => $item['id']]
                );
            }
        }

        return $dataSource;
    }
}
