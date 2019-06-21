<?php
namespace Aheadworks\Giftcard\Model\ResourceModel;

/**
 * History resource model
 */
class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_serializableFields = ['additional_info' => [null, []]];

    protected function _construct()
    {
        $this->_init('aw_giftcard_history', 'id');
    }
}