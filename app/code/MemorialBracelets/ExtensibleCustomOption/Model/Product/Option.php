<?php

namespace MemorialBracelets\ExtensibleCustomOption\Model\Product;

class Option extends \Magento\Catalog\Model\Product\Option
{
    /**
     * @return bool
     */
    public function hasValues()
    {
        return $this->getGroupByType() == self::OPTION_GROUP_SELECT;
    }
}
