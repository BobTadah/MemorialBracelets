<?php

namespace MemorialBracelets\IconOption\Model\ResourceModel\IconOption;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MemorialBracelets\IconOption\Model\IconOption as Model;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    public function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
