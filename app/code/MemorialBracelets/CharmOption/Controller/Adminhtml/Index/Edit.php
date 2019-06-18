<?php

namespace MemorialBracelets\CharmOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;
use MemorialBracelets\CharmOption\Model\CharmOption;
use MemorialBracelets\CharmOption\Model\CharmOptionFactory;

class Edit extends Action
{
    /** @var CharmOptionRepositoryInterface  */
    private $optionRepository;

    /** @var PageFactory  */
    protected $resultPageFactory;

    /** @var Registry  */
    protected $coreRegistry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        CharmOptionRepositoryInterface $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
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
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->optionRepository->getById($id);
                $this->coreRegistry->register('option_charm', $model);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This charm no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $result = $this->resultPageFactory->create();
        $result->setActiveMenu('MemorialBracelets_CharmOption::charmoption');
        $result->getConfig()->getTitle()->prepend('New Charm Option');
        return $result;
    }
}
