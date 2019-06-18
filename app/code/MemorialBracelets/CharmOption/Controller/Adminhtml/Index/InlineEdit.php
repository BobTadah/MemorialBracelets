<?php

namespace MemorialBracelets\CharmOption\Controller\Adminhtml\Index;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use MemorialBracelets\CharmOption\Api\CharmOptionInterface;
use MemorialBracelets\CharmOption\Model\CharmOption;
use MemorialBracelets\CharmOption\Model\CharmOptionRepository;

class InlineEdit extends AbstractAction
{
    /** @var CharmOptionRepository  */
    protected $repository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    public function __construct(Action\Context $context, CharmOptionRepository $repository, JsonFactory $jsonFactory)
    {
        parent::__construct($context);
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
            foreach (array_keys($postItems) as $charmId) {
                /** @var CharmOption $charm */
                $charm = $this->repository->getById($charmId);
                try {
                    $charm->setData(array_merge($charm->getData(), $postItems[$charmId]));
                    $this->repository->save($charm);
                } catch (\Exception $e) {
                    $messages[] = $this->getErrorWithCharm($charm, __($e->getMessage()));
                    $error = true;
                }
            }
        }

        return $resultJson->setData(['messages' => $messages, 'error' => $error]);
    }

    protected function getErrorWithCharm(CharmOptionInterface $charm, $errorText)
    {
        return '[Option ID: '.$charm->getId().'] '.$errorText;
    }
}
