<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardEmailTemplate;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Giftcard
 * @package Aheadworks\Giftcard\Block\Adminhtml\Product\Composite\Fieldset
 */
class Giftcard extends \Aheadworks\Giftcard\Block\Product\View
{
    /**
     * Amount select html ID
     *
     * @var string
     */
    protected $amountSelectId = 'aw_gc_amounts';

    /**
     * @var null|array
     */
    protected $amountOptions = null;

    /**
     * @var null|array
     */
    protected $templateOptions = null;

    /**
     * @var GiftcardEmailTemplate
     */
    protected $sourceEmailTemplate;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

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
     * @param PriceCurrencyInterface $priceCurrency
     * @param GiftcardEmailTemplate $sourceEmailTemplate
     * @param \Magento\Backend\Model\Auth\Session $authSession
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
        PriceCurrencyInterface $priceCurrency,
        GiftcardEmailTemplate $sourceEmailTemplate,
        \Magento\Backend\Model\Auth\Session $authSession,
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
        $this->sourceEmailTemplate = $sourceEmailTemplate;
        $this->authSession = $authSession;
    }

    /**
     * @return string
     */
    public function getAmountsSelectId()
    {
        return $this->amountSelectId;
    }

    /**
     * @return bool
     */
    public function hasAmountOptions()
    {
        return count($this->getAmountOptions()) > 0;
    }

    /**
     * @return bool
     */
    public function isAllowOpenAmount()
    {
        return (bool)$this->getProduct()->getData(TypeGiftCard::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT);
    }

    /**
     * @return float
     */
    public function getMinOpenAmount()
    {
        return $this->getProduct()->getData(TypeGiftCard::ATTRIBUTE_CODE_OPEN_AMOUNT_MIN);
    }

    /**
     * @return float
     */
    public function getMaxOpenAmount()
    {
        return $this->getProduct()->getData(TypeGiftCard::ATTRIBUTE_CODE_OPEN_AMOUNT_MAX);
    }

    /**
     * @return string
     */
    public function getAmountsOptionsHtml()
    {
        $html = '';
        $value = $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_AMOUNT, false);
        $custom = $value !== false;
        $optionTemplate = "<option value=\"%s\" %s>%s</option>";
        $html .= sprintf($optionTemplate, '', '', __('Choose an Amount...'));
        foreach ($this->getAmountOptions() as $option) {
            if ($option == $value) {
                $selected = 'selected=\'selected\'';
                $custom = false;
            } else {
                $selected = '';
            }
            $html .= sprintf(
                $optionTemplate,
                $option,
                $selected,
                __($this->priceCurrency->convertAndFormat(
                    $option,
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $this->getProduct()->getStoreId())
                )
            );
        }
        if ($this->isAllowOpenAmount()) {
            $html .= sprintf(
                $optionTemplate,
                'custom',
                $custom ? 'selected=\'selected\'' : '',
                __('Other Amount...')
            );
        }
        return $html;
    }

    /**
     * @return bool
     */
    public function hasTemplateOptions()
    {
        return count($this->getTemplateOptions()) > 0;
    }

    /**
     * @return string
     */
    public function getTemplatesOptionsHtml()
    {
        $html = '';
        $value = $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE, false);
        $optionTemplate = "<option value=\"%s\" %s>%s</option>";
        $html .= sprintf($optionTemplate, '', '', __('Choose a Template...'));
        foreach ($this->getTemplateOptions() as $option) {
            $html .= sprintf(
                $optionTemplate,
                $option['template'],
                $option['template'] == $value ? 'selected=\'selected\'' : '',
                $this->sourceEmailTemplate->getOptionText($option['template'])
            );
        }
        return $html;
    }

    /**
     * @return string
     */
    public function getCustomAmountOptionValue()
    {
        return $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_CUSTOM_AMOUNT);
    }

    /**
     * Retrieves sender name
     *
     * @return string
     */
    public function getSenderNameValue()
    {
        $senderName = $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME, false);
        if (!$senderName) {
            $senderName = $this->authSession->getUser()->getFirstname();
        }
        return $senderName;
    }

    /**
     * Retrieves sender email
     *
     * @return string
     */
    public function getSenderEmailValue()
    {
        $senderEmail = $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL, false);
        if (!$senderEmail) {
            $senderEmail = $this->authSession->getUser()->getEmail();
        }
        return $senderEmail;
    }

    /**
     * Retrieves recipient name
     *
     * @return string
     */
    public function getRecipientNameValue()
    {
        return $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME);
    }

    /**
     * Retrieves recipient email
     *
     * @return string
     */
    public function getRecipientEmailValue()
    {
        return $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL);
    }

    /**
     * Retrieves headline
     *
     * @return string
     */
    public function getHeadlineValue()
    {
        return $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_HEADLINE);
    }

    /**
     * Retrieves message
     *
     * @return string
     */
    public function getMessageValue()
    {
        return $this->getPreconfiguredOptionValue(TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE);
    }

    /**
     * @return string
     */
    public function getDisplayCurrencyCode()
    {
        return $this->priceCurrency
            ->getCurrency($this->getProduct()->getStoreId())
            ->getCurrencyCode()
            ;
    }

    /**
     * @param float $amount
     * @return float
     */
    public function formatPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat(
            $amount,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getProduct()->getStoreId()
        );
    }

    /**
     * @param $amount
     * @return float
     */
    public function convertPrice($amount)
    {
        return $this->priceCurrency->convertAndRound(
            $amount,
            $this->getProduct()->getStoreId()
        );
    }

    /**
     * Retrieves preconfigured option value by code
     *
     * @param string $code
     * @param string $default
     * @return mixed|string
     */
    protected function getPreconfiguredOptionValue($code, $default = '')
    {
        $product = $this->getProduct();
        $value = $product->getPreconfiguredValues()->getData($code);
        return $value ? $value : $default;
    }

    /**
     * @return string
     */
    public function getJsInitOptions()
    {
        return \Zend_Json::encode([
            'amountsSelector' => '#' . $this->getAmountsSelectId(),
            'customAmountSelector' => '#aw_gc_custom_amount'
        ]);
    }
}
