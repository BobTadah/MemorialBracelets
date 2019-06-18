<?php
namespace MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MemorialBracelets\CharmOption\Model\CharmOption;
use MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption as CharmOptionResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(CharmOption::class, CharmOptionResourceModel::class);
    }
}
