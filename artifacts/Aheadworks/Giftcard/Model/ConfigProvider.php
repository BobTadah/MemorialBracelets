<?php
namespace Aheadworks\Giftcard\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ConfigProvider implements ConfigProviderInterface, \Aheadworks\Giftcard\Api\ConfigProviderInterface
{
    const GIFTCARD_TOTAL_CODE = 'aw_giftcard';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var GiftcardRepository
     */
    protected $_giftcardRepository;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param GiftcardRepository $giftcardRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        PriceCurrencyInterface $priceCurrency,
        GiftcardRepository $giftcardRepository
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_priceCurrency = $priceCurrency;
        $this->_giftcardRepository = $giftcardRepository;
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        return [
            'aw_giftcards' => [
                'applied' => $this->_getAppliedGiftCardsData(),
                'customer' => $this->_getCustomerGiftCardsData()
            ]
        ];
    }

    /**
     * @return \Aheadworks\Giftcard\Api\Data\GiftcardInterface[]
     */
    public function getAppliedGiftCardsData()
    {
        $result = [];
        foreach ($this->_getAppliedGiftCardsData() as $data) {
            $result[] = new \Aheadworks\Giftcard\Model\Data\Giftcard($data);
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function _getAppliedGiftCardsData()
    {
        $data = [];
        $totals = $this->_checkoutSession->getQuote()->getTotals();
        if (isset($totals[self::GIFTCARD_TOTAL_CODE])) {
            $giftCardTotal = $totals[self::GIFTCARD_TOTAL_CODE];
            foreach ($giftCardTotal->getGiftCards() as $giftCard) {
                $data[] = [
                    'code' => $giftCard->getCode(),
                    'amount' => $giftCard->getGiftcardAmount(),
                    'removeUrl' => $this->_urlBuilder->getUrl(
                            'awgiftcard/card/remove',
                            [
                                'id' => $giftCard->getReferenceId(),
                                'code' => $giftCard->getCode()
                            ]
                        )
                ];
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function _getCustomerGiftCardsData()
    {
        $data = [];
        if ($this->_customerSession->isLoggedIn()) {
            $giftCards = $this->_giftcardRepository->getCustomerGiftCards(
                $this->_customerSession->getCustomer()->getEmail(),
                $this->_checkoutSession->getQuote()->getId()
            );
            foreach ($giftCards as $giftCard) {
                $data[] = [
                    'code' => $giftCard->getCode(),
                    'balance' => $this->_priceCurrency->convert($giftCard->getBalance())
                ];
            }
        }
        return $data;
    }
}
