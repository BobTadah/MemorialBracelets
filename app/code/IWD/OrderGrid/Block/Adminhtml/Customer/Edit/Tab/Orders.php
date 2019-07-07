<?php

namespace IWD\OrderGrid\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Block\Adminhtml\Edit\Tab\Orders as CustomerOrders;
use Magento\Customer\Controller\RegistryConstants;
use IWD\OrderGrid\Helper\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Sales\Helper\Reorder;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Grid\Extended;

class Orders extends CustomerOrders
{
    protected $configHelper;

    public function __construct(
        Config $configHelper,
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Reorder $salesReorder,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $backendHelper, $collectionFactory, $salesReorder, $coreRegistry, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        if ($this->configHelper->isEnable()) {
            $this->setAdditionalJavaScript($this->getColorJs());
        }
    }

    public function getRowClass($row)
    {
        return 'grid-color-row color-' . $row['status'];
    }

    protected function getColorJs()
    {
        $colorsArr = $this->configHelper->getOrdersColor();
        if (!empty($colorsArr)) {
            $styles = '';
            foreach ($colorsArr as $status => $color) {
                if (!empty($color)) {
                    $styles .= '.color-' . $status . '{ background-color:#' . $color . ';} ';
                }
            }
            return "var sheet = document.createElement('style')
                    sheet.innerHTML = \"" . $styles . "\";
                    document.body.appendChild(sheet);";
        }
        return '';
    }

    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->getReport('sales_order_grid_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'increment_id'
        )->addFieldToSelect(
            'customer_id'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'grand_total'
        )->addFieldToSelect(
            'order_currency_code'
        )->addFieldToSelect(
            'store_id'
        )->addFieldToSelect(
            'billing_name'
        )->addFieldToSelect(
            'shipping_name'
        )->addFieldToSelect(
            'status'
        )->addFieldToFilter(
            'customer_id',
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );

        $this->setCollection($collection);

        return Extended::_prepareCollection();
    }
}