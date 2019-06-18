<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use MemorialBracelets\SupportiveMessages\Model\SupportiveMessageRepository;

class InlineEdit extends AbstractAction
{
    /** @var SupportiveMessageRepository  */
    protected $repository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    public function __construct(
        PageFactory $pageFactory,
        Context $context,
        SupportiveMessageRepository $repository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($pageFactory, $context);
        $this->repository = $repository;
        $this->jsonFactory = $jsonFactory;
    }

    /** {@inheritdoc} */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if (!$this->getRequest()->getParam('isAjax')) {
            return $resultJson->setData(['messages' => [], 'error' => false]);
        }

        $postItems = $this->getRequest()->getParam('items', []);
        if (!count($postItems)) {
            $messages[] = __('No items provided to the server.');
            $error = true;
        } else {
            foreach (array_keys($postItems) as $messageId) {
                $message = $this->repository->getById($messageId);
                try {
                    $message->setData(array_merge($message->getData(), $postItems[$messageId]));
                    $this->repository->save($message);
                } catch (\Exception $e) {
                    $messages[] = $this->getErrorWithMessage($message, __($e->getMessage()));
                    $error = true;
                }
            }
        }

        return $resultJson->setData(['messages' => $messages, 'error' => $error]);
    }

    /** @return string */
    protected function getErrorWithMessage($message, $errorText)
    {
        return '[Message ID: '. $message->getId() . '] '. $errorText;
    }
}
