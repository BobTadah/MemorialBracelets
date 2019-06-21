<?php
namespace Aheadworks\Giftcard\Model\ResourceModel\Giftcard;

class Creditmemo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('aw_giftcard_creditmemo', 'id');
    }
}