<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

abstract class AbstractAction extends Action
{
    /** @var PageFactory */
    protected $pageFactory;

    public function __construct(PageFactory $pageFactory, Context $context)
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /** {@inheritdoc} */
    abstract public function execute();
}
