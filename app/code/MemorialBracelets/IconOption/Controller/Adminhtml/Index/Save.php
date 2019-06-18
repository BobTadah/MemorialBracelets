<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;
use MemorialBracelets\IconOption\Model\IconOptionFactory;

class Save extends Action
{
    /** @var IconOptionRepositoryInterface */
    protected $repository;

    /** @var IconOptionFactory */
    protected $factory;

    /** @var DataPersistorInterface */
    protected $persistor;

    public function __construct(Action\Context $context, IconOptionRepositoryInterface $repo, IconOptionFactory $factory, DataPersistorInterface $persistor)
    {
        $this->repository = $repo;
        $this->factory = $factory;
        $this->persistor = $persistor;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!isset($data['general'])) {
            return $result->setPath('*/*/');
        }

        // We only have one tab, so we only need the contents of that
        $data = $data['general'];

        // Convert is_active to boolean for data storage
        $is_active = isset($data['is_active']) && $data['is_active'] === '1';
        $data['is_active'] = $is_active;

        if (isset($data['icon'])) {
            $data['icon'] = $data['icon'][0]['file'];
        } else {
            $data['icon'] = null;
        }

        $model = $this->factory->create();
        if (isset($data['id']) && !empty($data['id'])) {
            $model->getResource()->load($model, $data['id']);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This icon no longer exists.'));
                return $result->setPath('*/*/');
            }
        }

        $model->setData($data);

        try {
            $model = $this->repository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the icon.'));
            $this->persistor->clear('option_icon');

            switch ($this->getRequest()->getParam('back', false)) {
                case false:
                    return $result->setPath('*/*/');
                case 'new':
                    return $result->setPath('*/*/new');
                default:
                    return $result->setPath('*/*/edit', ['id' => $model->getId()]);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the icon.'));
        }

        $this->persistor->set('option_icon', $data);
        return $result->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
    }
}
