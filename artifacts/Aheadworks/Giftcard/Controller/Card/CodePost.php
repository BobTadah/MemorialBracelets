<?php
namespace Aheadworks\Giftcard\Controller\Card;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Giftcard\Model\GiftcardFactory;

/**
 * Class CodePost
 * @package Aheadworks\Giftcard\Controller\Card
 */
class CodePost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var GiftcardFactory
     */
    protected $_giftCardFactory;

    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $_giftCardManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param GiftcardFactory $giftCardFactory
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        GiftcardFactory $giftCardFactory,
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_giftCardFactory = $giftCardFactory;
        $this->_giftCardManager = $giftCardManager;
        $this->_escaper = $escaper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $giftCardCode = $this->getRequest()->getParam('giftcard_code', false);
        if (!$giftCardCode) {
            $this->messageManager->addError(__('Please enter gift card code.'));
            return $this->_goBack();
        }
        /** @var $giftCardModel \Aheadworks\Giftcard\Model\Giftcard */
        $giftCardModel = $this->_giftCardFactory->create();
        $giftCardModel->loadByCode(trim($giftCardCode));
        if (!$giftCardModel->getId()) {
            $this->messageManager->addError(__('Gift Card Code "%1" is not valid.', $this->_escaper->escapeHtml($giftCardCode)));
            return $this->_goBack();
        }

        try {
            if ($this->getRequest()->getParam('remove') == 1) {

            } else {
                $giftCardModel->validate();
                if ($this->getRequest()->getParam('check') == 1) {
                    $this->_checkoutSession->setAwGiftCardCheckData($giftCardModel->getData());
                } else {
                    $result = $this->_giftCardManager->addToQuote($giftCardModel);
                    if (!$result->getSuccess()) {
                        throw new LocalizedException(__($result->getMessage()));
                    }
                    $this->messageManager->addSuccess(__('Gift Card Code "%1" has been applied.', $this->_escaper->escapeHtml($giftCardCode)));
                }
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_goBack();
        }
        return $this->_goBack();
    }

    protected function _goBack()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
