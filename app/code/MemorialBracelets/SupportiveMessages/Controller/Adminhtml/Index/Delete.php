<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageRepositoryInterface as RepoInterface;

class Delete extends AbstractAction
{
    /** @var RepoInterface  */
    protected $repository;

    public function __construct(PageFactory $pageFactory, Context $context, RepoInterface $repository)
    {
        $this->repository = $repository;
        parent::__construct($pageFactory, $context);
    }


    /** {@inheritdoc} */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create()->setPath('*/*/');

        $id = $this->getRequest()->getParam('id', false);
        if (!$id) {
            return $result;
        }

        $success = $this->repository->deleteById($id);

        if (!$success) {
            $this->messageManager->addErrorMessage(__('Something went wrong while deleting the message.'));
        } else {
            $this->messageManager->addSuccessMessage(__('Message deleted successfully.'));
        }

        return $result;
    }
}
