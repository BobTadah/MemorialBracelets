<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

use Magento\Backend\App\Action;

class Activate extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $giftCardManager;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->giftCardManager = $giftCardManager;
    }

    /**
     * Activate action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->giftCardManager->activate($this->getRequest()->getParam('id'));
            $this->messageManager->addSuccess(__('Gift card code was successfully activated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while activating gift card code'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
