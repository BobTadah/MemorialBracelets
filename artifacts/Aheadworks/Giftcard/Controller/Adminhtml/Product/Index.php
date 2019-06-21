<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Product;

class Index extends \Aheadworks\Giftcard\Controller\Adminhtml\Product
{
    /**
     * Gift Card Products list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Card Products'));
        $this->_getSession()->setBackToAwGiftCardProducts(false);
        return $resultPage;
    }
}
