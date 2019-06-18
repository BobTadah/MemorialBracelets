<?php

namespace MemorialBracelets\SupportiveMessages\Model\ResourceModel\SupportiveMessage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MemorialBracelets\SupportiveMessages\Model\SupportiveMessage as Model;
use MemorialBracelets\SupportiveMessages\Model\ResourceModel\SupportiveMessage as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
