<?php

namespace IWD\OrderGrid\Ui\Component\Listing\Columns;

use IWD\OrderGrid\Helper\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class StatusColor
 * @package IWD\OrderGrid\Ui\Component\Listing\Columns
 */
class StatusColor extends Column
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * StatusColor constructor.
     * @param Config $configHelper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Config $configHelper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($this->configHelper->isEnable()) {
            $colors = $this->configHelper->getOrdersColor();
            if (isset($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as &$item) {
                    $item['color'] = (isset($colors[$item['status']])
                        && !empty($colors[$item['status']])) ? $colors[$item['status']] : '';
                }
            }
        }
        return $dataSource;
    }
}
