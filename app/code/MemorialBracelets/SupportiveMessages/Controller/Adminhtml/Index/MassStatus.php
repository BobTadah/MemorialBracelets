<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageRepositoryInterface;
use MemorialBracelets\SupportiveMessages\Model\ResourceModel\SupportiveMessage\CollectionFactory;

class MassStatus extends AbstractAction
{
    /** @var Filter */
    protected $filter;

    /** @var CollectionFactory */
    protected $factory;

    /** @var SupportiveMessageRepositoryInterface */
    protected $repository;

    public function __construct(
        PageFactory $pageFactory,
        Context $context,
        Filter $filter,
        CollectionFactory $factory,
        SupportiveMessageRepositoryInterface $repository
    ) {
        $this->filter = $filter;
        $this->factory = $factory;
        $this->repository = $repository;
        parent::__construct($pageFactory, $context);
    }

    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        $baseCollection = $this->factory->create();
        $collection = $this->filter->getCollection($baseCollection);

        $size = $collection->getSize();

        $status = $this->getRequest()->getParam('status', null);
        if (!in_array($status, ['1', '0'])) {
            return $result->setPath('*/*/');
        }

        // convert to boolean
        $status = !!$status;

        $i = 0;

        /** SupportiveMessage $message */
        foreach ($collection as $message) {
            $message->setData('is_active', $status);
            try {
                $this->repository->save($message);
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
            $this->getMessageManager()->addNoticeMessage(__('%1 of %2 record(s) were successfully saved.', $i, $size));
        }

        return $result->setPath('*/*/');
    }
}
