<?php

namespace IWD\OrderGrid\Ui\Component\Listing;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use IWD\OrderGrid\Helper\Config;
use IWD\OrderGrid\Helper\Column as ColumnHelper;

/**
 * Class Columns
 * @package IWD\OrderGrid\Ui\Component\Listing
 */
class Columns extends Column
{

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var ColumnHelper
     */
    protected $columnHelper;

    /**
     * @var mixed
     */
    protected  $columnName;

    /**
     * Constructor
     * @param ColumnHelper $columnHelper
     * @param Config $configHelper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ColumnHelper $columnHelper,
        Config $configHelper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->columnHelper = $columnHelper;
        $this->configHelper = $configHelper;
        $this->columnName = $data['name'];
        if (!$this->configHelper->isActiveColumn($this->columnName) || !$this->configHelper->isEnable()) {
            $data = [];
        }
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
}
