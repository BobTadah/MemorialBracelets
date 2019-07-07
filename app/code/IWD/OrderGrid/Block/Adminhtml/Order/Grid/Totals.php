<?php

namespace IWD\OrderGrid\Block\Adminhtml\Order\Grid;

use Magento\Backend\Block\Template;

class Totals extends Template
{
    /**
     * @return bool
     */
    public function isGridTotalsEnabled()
    {
        return (bool)$this->_scopeConfig->getValue('iwd_ordergrid/total/enabled');
    }
}
