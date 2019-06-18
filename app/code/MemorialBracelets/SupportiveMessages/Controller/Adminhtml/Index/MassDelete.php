<?php

namespace MemorialBracelets\SupportiveMessages\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use MemorialBracelets\SupportiveMessages\Model\ResourceModel\SupportiveMessage\CollectionFactory;

class MassDelete extends AbstractAction
{
    /** @var Filter  */
    protected $filter;

    /** @var CollectionFactory  */
    protected $factory;

    public function __construct(PageFactory $pageFactory, Context $context, Filter $filter, CollectionFactory $factory)
    {
        $this->filter = $filter;
        $this->factory = $factory;
        parent::__construct($pageFactory, $context);
    }

    public function execute()
    {
        $baseCollection = $this->factory->create();
        $collection = $this->filter->getCollection($baseCollection);
        $size = $collection->getSize();

        $i = 0;
        foreach ($collection as $message) {
            $message->delete() ? ++$i : null;
        }

        if ($i == $size) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $size));
        } else {
            $this->messageManager->addNoticeMessage(__('%1 of %2 record(s) were successfully deleted.', $i, $size));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
