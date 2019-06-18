<?php

namespace MemorialBracelets\CheckHandling\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Controller\OrderInterface;

class Index extends Action implements OrderInterface
{
    protected $resultPage;
    /** @var ForwardFactory */
    protected $resultForwardFactory;
    /** @var OrderLoaderInterface */
    protected $orderLoader;

    public function __construct(
        Context $context,
        Page $resultPage,
        ForwardFactory $resultForwardFactory,
        OrderLoaderInterface $orderLoader
    ) {
        $this->resultPage = $resultPage;
        $this->orderLoader = $orderLoader;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws AuthorizationException
     * @throws NotFoundException
     */
    public function execute()
    {
        $order = $this->orderLoader->load($this->_request);
        if ($order instanceof ResultInterface) {
            return $order;
        }

        $pageId = 'check-order-instructions';
        /** @var Page $resultPage */
        $resultPage = $this->_objectManager->get('Magento\Cms\Helper\Page')->prepareResultPage($this, $pageId);
        if (!$resultPage) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $resultPage->addHandle('print');
        return $resultPage;
    }
}
