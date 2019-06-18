<?php

namespace MemorialBracelets\NameProduct\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\DataObject\IdentityInterface;

class Name extends Renderer implements IdentityInterface
{
    public function getProductForThumbnail()
    {
        return $this->getProduct();
    }

    public function getIdentities()
    {
        $identities = parent::getIdentities();
        if ($this->getItem()) {
            $identities = array_merge($identities, $this->getGroupedProduct()->getIdentities());
        }
        return $identities;
    }
}
