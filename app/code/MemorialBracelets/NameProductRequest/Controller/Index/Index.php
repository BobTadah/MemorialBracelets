<?php

namespace MemorialBracelets\NameProductRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Index extends Action
{
    /**
     * Dispatch request
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->getConfig()->getTitle()->set('Request a Name');
        return $result;
    }
}
