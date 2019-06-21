<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

use Magento\Backend\App\Action;

class Edit extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardFactory
     */
    protected $_giftcardModelFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Giftcard\Model\GiftcardFactory $giftcardFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_giftcardModelFactory = $giftcardFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var $giftcard \Aheadworks\Giftcard\Model\Giftcard */
        $giftcard = $this->_giftcardModelFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $giftcard->load($id);
            if (!$giftcard->getId()) {
                $this->messageManager->addError(__('This gift card no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/*');
            }
        }
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $giftcard->addData($data);
        }
        if (!$id && $this->_getSession()->hasAwGiftCardEmailTemplate()) {
            $giftcard->setEmailTemplate($this->_getSession()->getAwGiftCardEmailTemplate());
        }
        $this->_coreRegistry->register('aw_giftcard', $giftcard);

        $resultPage = $this->_getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Giftcard::home');
        $resultPage->getConfig()->getTitle()->prepend(
            $giftcard->getId() ?  __('Edit Gift Card Code') : __('New Gift Card Code')
        );
        $resultPage->getLayout()->getBlock('breadcrumbs')
            ->addCrumb(
                'giftcard',
                ['label' => __('Gift Card Codes'), 'link' =>$this->getUrl('*/*/index')]
            )
            ->addCrumb(
                'edit',
                ['label' => $giftcard->getId() ? __('Edit Gift Card Code "%1"', $giftcard->getCode()) : __('New Gift Card Code')]
            )
        ;

        return $resultPage;
    }
}
