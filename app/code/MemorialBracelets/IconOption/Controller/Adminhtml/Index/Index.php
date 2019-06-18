<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Framework\App\ResponseInterface;

class Index extends AbstractPageAction
{
    /** {@inheritdoc} */
    public function execute()
    {
        $result = $this->resultPageFactory->create();
        $result->setActiveMenu('MemorialBracelets_IconOption::iconoption');
        $result->getConfig()->getTitle()->prepend('Icon Options');
        return $result;
    }
}
