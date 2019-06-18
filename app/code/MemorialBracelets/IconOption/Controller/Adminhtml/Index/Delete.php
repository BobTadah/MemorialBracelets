<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;

class Delete extends Action
{
    /** @var IconOptionRepositoryInterface */
    protected $repository;

    public function __construct(Action\Context $context, IconOptionRepositoryInterface $repository)
    {
        $this->repository = $repository;
        parent::__construct($context);
    }

    /** {@inheritdoc} */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id', false);
        if (!$id) {
            return $result->setPath('*/*/');
        }

        $success = $this->repository->deleteById($id);

        if (!$success) {
            $this->messageManager->addErrorMessage(__('Something went wrong while deleting the icon.'));
        } else {
            $this->messageManager->addSuccessMessage(__('You deleted the icon.'));
        }

        return $result->setPath('*/*/');
    }
}
