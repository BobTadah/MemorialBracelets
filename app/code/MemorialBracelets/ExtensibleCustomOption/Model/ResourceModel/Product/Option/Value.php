<?php

namespace MemorialBracelets\ExtensibleCustomOption\Model\ResourceModel\Product\Option;

use Magento\Framework\Model\AbstractModel;

class Value extends \Magento\Catalog\Model\ResourceModel\Product\Option\Value
{
    public function _beforeSave(AbstractModel $object)
    {
        return parent::_beforeSave($object);
    }

    public function _afterSave(AbstractModel $object)
    {
        return parent::_afterSave($object);
    }
}
