<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;
use MemorialBracelets\IconOption\Model\IconOption;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption\CollectionFactory;

class MassStatus extends Action
{
    /** @var Filter  */
    protected $filter;

    /** @var CollectionFactory  */
    protected $factory;

    /** @var IconOptionRepositoryInterface  */
    protected $repository;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $factory,
        IconOptionRepositoryInterface $repository
    ) {
        $this->filter = $filter;
        $this->factory = $factory;
        $this->repository = $repository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        $baseCollection = $this->factory->create();
        $collection = $this->filter->getCollection($baseCollection);

        $size = $collection->getSize();

        $status = $this->getRequest()->getParam('status', null);
        if (!in_array($status, ['0','1'])) {
            return $result->setPath('*/*/');
        }

        switch ($status) {
            case '1':
                $status = true;
                break;
            case '0':
                $status = false;
                break;
        }

        $i = 0;

        /** @var IconOption $icon */
        foreach ($collection as $icon) {
            $icon->setData('is_active', $status);
            try {
                $this->repository->save($icon);
                ++$i;
            } catch (LocalizedException $e) {
                $this->getMessageManager()->addExceptionMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->getMessageManager()->addExceptionMessage($e, __('An unexpected error occurred'));
            }
        }

        if ($i == $size) {
            $this->getMessageManager()->addSuccessMessage(__('A total of %1 record(s) were updated.', $size));
        } elseif ($i > 0 && $i < $size) {
            $this->getMessageManager()->addNoticeMessage(__('%1 of %2 record(s) were updated.', $i, $size));
        }

        return $result->setPath('*/*/');
    }
}
