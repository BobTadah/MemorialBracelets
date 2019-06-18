<?php

namespace MemorialBracelets\IconOption\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class IconOption extends AbstractDb
{
    const FIELD_ID = 'entity_id';
    const FIELD_TITLE = 'title';
    const FIELD_ICON = 'icon';
    const FIELD_POSITION = 'position';
    const FIELD_PRICE = 'price';
    const FIELD_PRICE_TYPE = 'price_type';
    const FIELD_IS_ACTIVE = 'is_active';
    const FIELD_CREATION_TIME = 'creation_time';
    const FIELD_UPDATE_TIME = 'update_time';

    /** {@inheritdoc} */
    protected function _construct()
    {
        return $this->_init('option_icon', 'entity_id');
    }
}
