<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Giftcard\Model\Email\Sender;
use Aheadworks\Giftcard\Model\Source\Giftcard\EmailTemplate;

class Save extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardFactory
     */
    protected $giftcardModelFactory;

    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Aheadworks\Giftcard\Model\GiftcardFactory $giftcardFactory
     * @param Sender $sender
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Giftcard\Model\GiftcardFactory $giftcardFactory,
        Sender $sender
    ) {
        $this->giftcardModelFactory = $giftcardFactory;
        $this->sender = $sender;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Aheadworks\Giftcard\Model\Giftcard $giftCardCode */
            $giftCardCode = $this->giftcardModelFactory->create();
            $id = $this->getRequest()->getParam('giftcard_id');
            $back = $this->getRequest()->getParam('back');
            $send = $this->getRequest()->getParam('send');
            if ($id) {
                $giftCardCode->load($id);
                if (
                    isset($data['balance']) &&
                    $giftCardCode->getOrigData('balance') != $data['balance']
                ) {
                    $data['initial_balance'] = $data['balance'];
                }
            }
            $data['id'] = $id;
            if (isset($data['expire_at'])) {
                if ($data['expire_at'] != '' && !is_null($data['expire_at'])) {
                    $locale = new \Zend_Locale($this->_localeResolver->getLocale());
                    $date = new \Zend_Date(null, null, $locale);
                    $date->setDate($data['expire_at'], $locale->getTranslation(null, 'date', $locale));
                    $data['expire_at'] = $date->toString('YYYY-MM-dd H:m:s');
                }
            }
            $giftCardCode->addData($data);
            try {
                $giftCardCode->save();
                $this->messageManager->addSuccess(__('Gift card code was successfully saved.'));
                $this->_getSession()->setAwGiftCardEmailTemplate($giftCardCode->getEmailTemplate());
                $this->_getSession()->setFormData(false);
                if ($back == 'edit') {
                    return $resultRedirect->setPath('*/*/' . $back, ['id' => $giftCardCode->getId(), '_current' => true]);
                }
                if ($send && $giftCardCode->getEmailTemplate() != EmailTemplate::DO_NOT_SEND_VALUE) {
                    $this->sender->sendGiftCardCode($giftCardCode);
                    $this->messageManager->addSuccess(__('Email was successfully sent.'));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the gift card code.'));
            }
            $data['id'] = $id;
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
