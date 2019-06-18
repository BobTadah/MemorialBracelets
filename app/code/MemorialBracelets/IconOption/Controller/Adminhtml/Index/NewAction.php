<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class NewAction extends Action
{
    /** {@inheritdoc} */
    public function execute()
    {
        return $this->_forward('edit');
    }
}
