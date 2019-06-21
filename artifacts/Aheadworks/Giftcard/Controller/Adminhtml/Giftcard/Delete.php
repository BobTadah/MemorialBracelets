<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

use Magento\Backend\App\Action;

class Delete extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardFactory
     */
    protected $_giftcardModelFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Giftcard\Model\GiftcardFactory $giftcardFactory
    ) {
        $this->_giftcardModelFactory = $giftcardFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $giftcard = $this->_giftcardModelFactory->create();
        if ($id && $giftcard->load($id)->getId()) {
            try {
                $giftcard->delete();
                $this->messageManager->addSuccess(__('Gift card code was successfully deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/');
        }

        $this->messageManager->addError(__('Gift card code doesn\'t exist.'));
        return $resultRedirect->setPath('*/*/');
    }
}
