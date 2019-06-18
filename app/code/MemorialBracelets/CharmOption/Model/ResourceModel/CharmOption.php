<?php

namespace MemorialBracelets\CharmOption\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CharmOption extends AbstractDb
{
    const FIELD_TITLE = 'title';
    const FIELD_POSITION = 'position';
    const FIELD_PRICE = 'price';
    const FIELD_PRICE_TYPE = 'price_type';
    const FIELD_IS_ACTIVE = 'is_active';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('option_charm', 'id');
    }
}
