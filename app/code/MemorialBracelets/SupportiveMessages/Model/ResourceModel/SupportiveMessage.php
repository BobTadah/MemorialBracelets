<?php

namespace MemorialBracelets\SupportiveMessages\Model\ResourceModel;

class SupportiveMessage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $idField = 'entity_id';
        $table = 'memorialbracelets_supportivemessage';

        $this->_init($table, $idField);
    }
}
