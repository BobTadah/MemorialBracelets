<?php
namespace Aheadworks\Giftcard\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Recipient extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardFactory
     */
    protected $giftcardFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param ContextInterface $context
     * @param \Aheadworks\Giftcard\Model\GiftcardFactory $giftcardFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Aheadworks\Giftcard\Model\GiftcardFactory $giftcardFactory,
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
        $this->giftcardFactory = $giftcardFactory;
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
                $giftcard = $this->giftcardFactory->create()->load($item['id']);
                if ($customer = $giftcard->getRecipientCustomer()) {
                    $item[$fieldName . '_url'] = $this->url->getUrl(
                        'customer/index/edit',
                        ['id' => $customer->getId()]
                    );
                }
            }
        }
        return $dataSource;
    }
}
