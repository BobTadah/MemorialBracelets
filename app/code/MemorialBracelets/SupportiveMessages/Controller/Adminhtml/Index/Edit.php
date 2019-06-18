<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageRepositoryInterface;

class Edit extends AbstractAction
{
    /** @var Registry */
    protected $registry;

    /** @var SupportiveMessageRepositoryInterface */
    protected $repository;

    public function __construct(
        PageFactory $pageFactory,
        Context $context,
        Registry $registry,
        SupportiveMessageRepositoryInterface $repository
    ) {
        $this->registry = $registry;
        $this->repository = $repository;
        parent::__construct($pageFactory, $context);
    }

    /** {@inheritdoc} */
    public function execute()
    {
        if ($this->getId()) {
            try {
                $model = $this->repository->getById($this->getId());
                $this->registry->register('supportivemessage', $model);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This message no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }
        $result = $this->pageFactory->create();
        $result->setActiveMenu('MemorialBracelets_CharmOption::charmoption');
        $title = $this->isNew() ? 'New Supportive Message ' : 'Edit Supportive Message';
        $result->getConfig()->getTitle()->prepend($title);
        return $result;
    }

    public function getId()
    {
        return $this->getRequest()->getParam('id', false);
    }

    public function isNew()
    {
        return false === $this->getId();
    }
}
