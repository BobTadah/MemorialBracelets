<?php

namespace MemorialBracelets\NameProduct\Block\Stockqty\Type;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Block\Stockqty\Composite;
use Magento\Framework\DataObject\IdentityInterface;

class Name extends Composite implements IdentityInterface
{
    /**
     * Retrieve child products
     *
     * @return Product[]
     */
    protected function _getChildProducts()
    {
        return $this->getProduct()->getTypeInstance()->getConfiguredProducts($this->getProduct());
    }

    public function getIdentities()
    {
        $identities = [];
        foreach ($this->_getChildProducts() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }
}
