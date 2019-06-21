<?php
namespace Aheadworks\Giftcard\Controller\Card;

use Aheadworks\Giftcard\Model\Giftcard;

/**
 * Class Remove
 * @package Aheadworks\Giftcard\Controller\Card
 */
class Remove extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $_giftCardManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
    ) {
        $this->_giftCardManager = $giftCardManager;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_giftCardManager->removeFromQuote($id);
                $this->messageManager->addSuccess(__('Gift Card Code "%1" has been removed.', $this->getRequest()->getParam('code')));
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Cannot remove gift card code.'));
            }
        }
        return $this->_goBack();
    }

    protected function _goBack()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->getBackUrl($this->_redirect->getRefererUrl()));
        return $resultRedirect;
    }

    protected function getBackUrl($referer = null)
    {
        if($referer) {
            if (strpos($referer, '#payment') !== false) {
            } else {
                $referer .= '#payment';
            }
            return $referer;
        }
        return $this->_url->getUrl('*/*');
    }
}
