<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;

class Edit extends AbstractPageAction
{
    /** @var Registry */
    protected $coreRegistry;

    /** @var IconOptionRepositoryInterface */
    protected $repository;

    public function __construct(Context $context, PageFactory $resultPageFactory, Registry $registry, IconOptionRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->coreRegistry = $registry;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            try {
                $model = $this->repository->getById($id);
                $this->coreRegistry->register('option_icon', $model);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This icon no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $result = $this->resultPageFactory->create();
        $result->setActiveMenu('MemorialBracelets_IconOption::iconoption');
        $result->getConfig()->getTitle()->prepend($id ? 'Edit Icon' : 'New Icon');
        return $result;
    }
}
