<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

class Index extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Giftcard::home');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Giftcard::home');
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Card Codes'));
        return $resultPage;
    }
}
