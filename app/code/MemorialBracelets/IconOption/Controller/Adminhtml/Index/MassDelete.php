<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Ui\Component\MassAction\Filter;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;
use MemorialBracelets\IconOption\Model\IconOption;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption\CollectionFactory;

class MassDelete extends Action
{
    /** @var Filter */
    protected $filter;

    /** @var CollectionFactory */
    protected $factory;

    /** @var IconOptionRepositoryInterface */
    protected $repository;

    public function __construct(Action\Context $context, Filter $filter, CollectionFactory $factory, IconOptionRepositoryInterface $repository)
    {
        $this->filter = $filter;
        $this->factory = $factory;
        $this->repository = $repository;
        parent::__construct($context);
    }

    /** {@inheritdoc} */
    public function execute()
    {
        $baseCollection = $this->factory->create();
        $collection = $this->filter->getCollection($baseCollection);
        $size = $collection->getSize();

        $i = 0;
        /** @var IconOption $icon */
        foreach ($collection as $icon) {
            try {
                $this->repository->delete($icon) ? ++$i : null;
            } catch (CouldNotDeleteException $e) {
                // [house engulfed by fire] this is fine.
            }
        }

        if ($i == $size) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $size));
        } else {
            $this->messageManager->addNoticeMessage(__('%1 of %2 record(s) were deleted.', $i, $size));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
