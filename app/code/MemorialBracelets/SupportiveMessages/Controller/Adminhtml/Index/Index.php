<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;

class Index extends AbstractAction
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var Page $result */
        $result = $this->pageFactory->create();
        $result->setActiveMenu('MemorialBracelets_SupportiveMessages::messages');
        $result->getConfig()->getTitle()->prepend('Supportive Messages');
        return $result;
    }
}
