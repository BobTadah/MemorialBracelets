<?php

namespace MemorialBracelets\CharmOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use MemorialBracelets\CharmOption\Model\CharmOptionFactory;

class Save extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var CharmOptionFactory */
    protected $optionFactory;

    /** @var DataPersistorInterface */
    protected $dataPersistor;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CharmOptionFactory $charmOptionFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->optionFactory = $charmOptionFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!isset($data['general'])) {
            return $result->setPath('*/*/');
        }

        // General is the "default tab" and only tab
        $data = $data['general'];

        // Convert is_active to boolean for data storage
        $is_active = isset($data['is_active']) && $data['is_active'] === '1';
        $data['is_active'] = $is_active;

        if (isset($data['icon'])) {
            $data['icon'] = $data['icon'][0]['file'];
        } else {
            $data['icon'] = null;
        }

        $model = $this->optionFactory->create();
        if (isset($data['id']) && !empty($data['id'])) {
            $model->load($data['id']);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This charm no longer exists.'));
                return $result->setPath('*/*/');
            }
        }

        $model->setData($data);

        try {
            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the charm.'));
            $this->dataPersistor->clear('option_charm');

            switch ($this->getRequest()->getParam('back', false)) {
                case false:
                    return $result->setPath('*/*/');
                case 'new':
                    return $result->setPath('*/*/new');
                default:
                    return $result->setPath('*/*/edit', ['id' => $model->getId()]);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the charm.'));
        }

        $this->dataPersistor->set('option_charm', $data);
        return $result->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
    }
}
