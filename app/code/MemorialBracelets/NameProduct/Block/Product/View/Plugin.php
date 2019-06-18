<?php

namespace MemorialBracelets\NameProduct\Block\Product\View;

use Magento\Catalog\Block\Product\View;
use MemorialBracelets\NameProduct\Model\Product\Type\Name as NameType;

class Plugin
{
    public function afterShouldRenderQuantity(View $subject, $result)
    {
        if ($result || $subject->getProduct()->getTypeInstance() instanceof NameType) {
            return true;
        }
        return $result;
    }
}
