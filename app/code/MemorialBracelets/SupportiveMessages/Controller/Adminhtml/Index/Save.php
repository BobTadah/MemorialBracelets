<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use MemorialBracelets\SupportiveMessages\Model\SupportiveMessage;
use MemorialBracelets\SupportiveMessages\Model\SupportiveMessageFactory;

class Save extends AbstractAction
{
    /** @var SupportiveMessageFactory  */
    protected $factory;

    /** @var DataPersistorInterface  */
    protected $persistor;

    public function __construct(
        PageFactory $pageFactory,
        Context $context,
        SupportiveMessageFactory $factory,
        DataPersistorInterface $persistor
    ) {
        parent::__construct($pageFactory, $context);
        $this->factory = $factory;
        $this->persistor = $persistor;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!isset($data['general'])) {
            // No form data
            return $result->setPath('*/*/');
        }

        // The default tab is the only tab so lets focus on that
        $data = $data['general'];

        $is_active = isset($data['is_active']) && $data['is_active'] === '1';
        $data['is_active'] = $is_active;

        $model = $this->factory->create();
        if (isset($data['entity_id']) && !empty($data['entity_id'])) {
            $model->load($data['entity_id']);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This message no longer exists.'));
                return $result->setPath('*/*/');
            }
        }

        $model->setData($data);

        try {
            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the message.'));
            $this->persistor->clear('supportivemessage');

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
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        $this->persistor->set('supportivemessage', $data);
        return $result->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
    }
}
