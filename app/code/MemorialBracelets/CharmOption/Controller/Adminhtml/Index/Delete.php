<?php

namespace MemorialBracelets\CharmOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;
use MemorialBracelets\CharmOption\Model\CharmOptionFactory;

class Delete extends Action
{
    /** @var CharmOptionRepositoryInterface */
    protected $repository;

    public function __construct(Context $context, CharmOptionRepositoryInterface $repository)
    {
        $this->repository = $repository;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id', false);
        if (!$id) {
            return $result->setPath('*/*/');
        }

        $success = $this->repository->deleteById($id);

        if (!$success) {
            $this->messageManager->addErrorMessage(__('Something went wrong while deleting the charm.'));
        } else {
            $this->messageManager->addSuccessMessage(__('Charm successfully deleted.'));
        }
        return $result->setPath('*/*/');
    }
}
