<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use MemorialBracelets\IconOption\Api\IconOptionInterface;
use MemorialBracelets\IconOption\Repository\IconOption;

class InlineEdit extends Action
{
    /** @var IconOption  */
    protected $repository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    public function __construct(Action\Context $context, IconOption $repo, JsonFactory $jsonFactory)
    {
        parent::__construct($context);
        $this->repository = $repo;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
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
            foreach (array_keys($postItems) as $iconId) {
                /** @var \MemorialBracelets\IconOption\Model\IconOption $icon */
                $icon = $this->repository->getById($iconId);
                try {
                    $icon->setData(array_merge($icon->getData(), $postItems[$iconId]));
                    $this->repository->save($icon);
                } catch (\Exception $e) {
                    $messages[] = $this->getErrorWithIcon($icon, __($e->getMessage()));
                    $error = true;
                }
            }
        }

        return $resultJson->setData(['messages' => $messages, 'error' => $error]);
    }

    protected function getErrorWithIcon(IconOptionInterface $icon, $errorText)
    {
        return '[Option ID: '.$icon->getId().'] '.$errorText;
    }
}
