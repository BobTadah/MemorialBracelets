<?php
namespace Aheadworks\Giftcard\Block\Giftcard;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Codes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var bool|array
     */
    protected $_giftCardCodes = false;

    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardRepository
     */
    protected $_giftcardRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Giftcard::giftcard/codes.phtml';

    /**
     * @param Context $context
     * @param \Aheadworks\Giftcard\Model\GiftcardRepository $giftcardRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Aheadworks\Giftcard\Model\GiftcardRepository $giftcardRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_giftcardRepository = $giftcardRepository;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        return count($this->getGiftCardCodes()) > 0;
    }

    public function getGiftCardCodes()
    {
        if (!$this->_giftCardCodes) {
            $this->_giftCardCodes = [];
            if ($this->_customerSession->isLoggedIn()) {
                $this->_giftCardCodes = $this->_giftcardRepository->getCustomerGiftCards(
                    $this->_customerSession->getCustomer()->getEmail(),
                    $this->_checkoutSession->getQuote()->getId()
                );
            }
        }
        return $this->_giftCardCodes;
    }

    /**
     * @param float $amount
     * @return float
     */
    public function formatPrice($amount)
    {
        return $this->_priceCurrency->convertAndFormat($amount);
    }

    /**
     * @param string $code
     * @return string
     */
    public function getApplyUrl($code)
    {
        return $this->getUrl('awgiftcard/card/codePost', ['giftcard_code' => $code]);
    }
}
