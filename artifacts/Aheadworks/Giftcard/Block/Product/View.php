<?php
namespace Aheadworks\Giftcard\Block\Product;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class View
 * @package Aheadworks\Giftcard\Block\Product
 */
class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var \Magento\Quote\Model\Quote\Item|null
     */
    protected $_quoteItem = null;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->jsLayout = isset($data['jsLayout']) ? $data['jsLayout'] : [];
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return $this
     */
    public function setQuoteItem($item)
    {
        $this->_quoteItem = $item;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFixedAmount()
    {
        $amountOptions = $this->getAmountOptions();
        return array_shift($amountOptions);
    }

    /**
     * @return bool
     */
    public function isFixedAmount()
    {
        return (count($this->getAmountOptions()) == 1) && !$this->isAllowOpenAmount();
    }

    /**
     * @return bool
     */
    public function isAllowDesignSelect()
    {
        return !$this->getProduct()->getTypeInstance()->isTypePhysical($this->getProduct()) && !$this->isSingleDesign();
    }

    /**
     * @return bool
     */
    public function isSingleDesign()
    {
        return count($this->getTemplateOptions($this->getProduct())) == 1;
    }

    /**
     * @return mixed
     */
    public function getTemplateValue()
    {
        $options = $this->getTemplateOptions($this->getProduct());
        return $options[0]['template'];
    }

    /**
     * @return bool
     */
    public function isAllowMessage()
    {
        return (bool)$this->getProduct()->getData(TypeGiftCard::ATTRIBUTE_CODE_ALLOW_MESSAGE);
    }

    /**
     * @return bool
     */
    public function canRenderOptions()
    {
        return (
            $this->getProduct()->isSaleable() &&
                ($this->isAllowOpenAmount() || count($this->getAmountOptions()) > 0)
        );
    }

    /**
     * @return bool
     */
    public function canRenderDescription()
    {
        $description = $this->getDescription();
        return !empty($description);
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->getProduct()->getData(TypeGiftCard::ATTRIBUTE_CODE_DESCRIPTION);
    }

    /**
     * @return bool
     */
    public function allowedEmail()
    {
        return !$this->getProduct()->getTypeInstance()->isTypePhysical($this->getProduct());
    }

    /**
     * @return bool
     */
    protected function isConfigure()
    {
        return $this->_quoteItem && $this->_quoteItem->getId();
    }

    /**
     * @param string $code
     * @return bool|mixed
     */
    protected function getQuoteItemOption($code)
    {
        $option = $this->_quoteItem->getOptionByCode($code);
        return $option ? $option->getValue() : false;
    }

    /**
     * @return string
     */
    public function getDisplayCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @return bool
     */
    public function isAllowPreview()
    {
        return !$this->getProduct()->getTypeInstance()->isTypePhysical(
            $this->getProduct()
        );
    }

    /**
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->_urlBuilder->getUrl('awgiftcard/product/preview', ['store' => $this->getProduct()->getStoreId(), '_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return bool
     */
    public function isAllowOpenAmount()
    {
        return (bool)$this->getProduct()->getData(TypeGiftCard::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT);
    }

    /**
     * @return array
     */
    protected function getAmountOptions()
    {
        return $this->getProduct()->getTypeInstance()->getAmountOptions(
            $this->getProduct()
        );
    }

    /**
     * @return array
     */
    protected function getTemplateOptions()
    {
        return $this->getProduct()->getTypeInstance()->getTemplateOptions(
            $this->getProduct()
        );
    }

    /**
     * @return string
     */
    public function getJsGiftCardModelOptions()
    {
        return \Zend_Json::encode([
            'optionsLoadUrl' => $this->_urlBuilder->getUrl('awgiftcard/product/getOptions', ['product' => $this->getProduct()->getId(), '_secure' => $this->getRequest()->isSecure()])
        ]);
    }

    /**
     * @return string
     */
    public function getJsProductData()
    {
        return \Zend_Json::encode([
            'amount' => $this->getProductOptionValue(TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS, false),
            'templateValue' => $this->getProductOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE),
            'recipientName' => $this->getProductOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME),
            'recipientEmail' => $this->getProductOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL),
            'senderName' => $this->getProductOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME),
            'senderEmail' => $this->getProductOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL),
            'headline' => $this->getProductOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_HEADLINE),
            'message' => $this->getProductOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE)
        ]);
    }

    /**
     * Retrieves Gift Card Product option value by code
     *
     * @param string $code
     * @param mixed $default
     * @return bool|mixed|string
     */
    protected function getProductOptionValue($code, $default = '')
    {
        if ($this->isConfigure()) {
            $value = $this->getQuoteItemOption($code);
            if ($code == TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS) {
                $buyRequest = $this->getQuoteItemOption('info_buyRequest');
                if (is_string($buyRequest)) {
                    $buyRequest = unserialize($buyRequest);
                }
                if ($buyRequest[TypeGiftCard::BUY_REQUEST_ATTR_CODE_AMOUNT] == 'custom') {
                    $value = $this->priceCurrency->convertAndRound($value);
                }
            }
            return $value ? $value : $default;
        }
        return $default;
    }
}
