<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

class NewAction extends AbstractAction
{

    /** {@inheritdoc} */
    public function execute()
    {
        return $this->_forward('edit');
    }
}
