<?php

namespace MemorialBracelets\NameProduct\Block\Product\Name;

use Magento\Backend\Block\Catalog\Product\Tab\Container;

class AssociatedProducts extends Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('name_product_container');
    }

    public function getTabLabel()
    {
        // TODO Likely Change
        return __('Name Products');
    }

    public function getParentTab()
    {
        return 'product-details';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        $this->setData('opened', true);
        return $this;
    }
}
