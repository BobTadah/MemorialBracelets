<?php

namespace MemorialBracelets\CharmOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;
use MemorialBracelets\CharmOption\Model\CharmOption;
use MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption\CollectionFactory;

class MassStatus extends Action
{
    /** @var Filter  */
    protected $filter;

    /** @var CollectionFactory  */
    protected $factory;

    /** @var CharmOptionRepositoryInterface  */
    protected $repository;

    public function __construct(Action\Context $context, Filter $filter, CollectionFactory $factory, CharmOptionRepositoryInterface $repository)
    {
        $this->filter = $filter;
        $this->factory = $factory;
        $this->repository = $repository;
        parent::__construct($context);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        $baseCollection = $this->factory->create();
        $collection = $this->filter->getCollection($baseCollection);

        $size = $collection->getSize();

        $status = $this->getRequest()->getParam('status', null);
        if (!in_array($status, ['1', '2'])) {
            return $result->setPath('*/*/');
        }

        // Convert parameter to truthy
        switch ($status) {
            case '1':
                $status = true;
                break;
            case '2':
                $status = false;
                break;
        }

        $i = 0;

        /** @var CharmOption $charm */
        foreach ($collection as $charm) {
            $charm->setData('is_active', $status);
            try {
                $this->repository->save($charm);
                ++ $i;
            } catch (LocalizedException $e) {
                $this->getMessageManager()->addExceptionMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->getMessageManager()->addExceptionMessage($e, __('An unexpected error occurred'));
            }
        }

        if ($i == $size) {
            $this->getMessageManager()->addSuccessMessage(__('A total of %1 record(s) were updated.', $size));
        } elseif ($i > 0 && $i < $size) {
            $this->getMessageManager()->addNoticeMessage(__('%1 of %2 record(s) were successfully saved.', $i, $size));
        }

        return $result->setPath('*/*/');
    }
}
