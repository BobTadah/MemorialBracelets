<?php
namespace Aheadworks\Giftcard\Block\Giftcard;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Aheadworks\Giftcard\Model\Source\Giftcard\Status;

/**
 * Gift Card info block
 * @package Aheadworks\Giftcard\Block\Giftcard
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var bool|\Magento\Framework\DataObject
     */
    protected $_statusInfo = false;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Aheadworks\Giftcard\Model\Source\Giftcard\Status
     */
    protected $_sourceStatus;

    /**
     * @var \Aheadworks\Giftcard\Model\Source\Giftcard\IsUsed
     */
    protected $sourceIsUsed;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Giftcard::giftcard/info.phtml';

    /**
     * Info constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Status $sourceStatus
     * @param \Aheadworks\Giftcard\Model\Source\Giftcard\IsUsed $sourceIsUsed
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Aheadworks\Giftcard\Model\Source\Giftcard\Status $sourceStatus,
        \Aheadworks\Giftcard\Model\Source\Giftcard\IsUsed $sourceIsUsed,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_sourceStatus = $sourceStatus;
        $this->sourceIsUsed = $sourceIsUsed;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Framework\DataObject
     */
    public function getGiftCardStatusInfo()
    {
        if (!$this->_statusInfo && $this->_checkoutSession->hasAwGiftCardCheckData()) {
            /** @var $giftCardModel \Aheadworks\Giftcard\Model\Giftcard */
            $giftCardData = $this->_checkoutSession->getAwGiftCardCheckData();
            $this->_statusInfo = new \Magento\Framework\DataObject([
                'code' => $giftCardData['code'],
                'status' => $this->_sourceStatus->getOptionByValue($giftCardData['state']),
                'is_used' => $this->sourceIsUsed->getOptionByValue($giftCardData['is_used']),
                'balance' => $this->_priceCurrency->convertAndFormat($giftCardData['balance'])
            ]);
            if (isset($giftCardData['expire_at'])) {
                $expiredAt = $this->_localeDate
                    ->date($giftCardData['expire_at'], null, false)
                    ->setTimezone(new \DateTimeZone($this->_localeDate->getConfigTimezone()))
                ;
                $this->_statusInfo->setData('expire_at', $this->formatDate($expiredAt));
            }
            $this->_checkoutSession->unsAwGiftCardCheckData();
        }
        return $this->_statusInfo;
    }
}
