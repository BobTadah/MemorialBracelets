<?php
namespace Aheadworks\Giftcard\Block\Checkout\Cart\Item;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Gift Card Product items Renderer
 * @package Aheadworks\Giftcard\Block\Checkout\Cart\Item
 */
class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * Gift Card Product option codes to rendering
     *
     * @var array
     */
    protected $renderOptionCodes = [
        TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME,
        TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME,
        TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL,
        TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME,
        TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL,
        TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE,
    ];

    /**
     * @var \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration
     */
    protected $giftCardConfiguration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $messageInterpretationStrategy
     * @param \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $messageInterpretationStrategy,
        \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );
        $this->giftCardConfiguration = $giftCardConfiguration;
    }

    /**
     * Get list of all options for product
     *
     * @return array
     */
    public function getOptionList()
    {
        return array_merge(
            $this->_getGiftcardOptions(),
            parent::getOptionList()
        );
    }

    /**
     * Get list of Gift Card options for product
     *
     * @return array
     */
    protected function _getGiftcardOptions()
    {
        $data = [];
        foreach ($this->renderOptionCodes as $code) {
            $data[$code] = $this->_getCustomOption($code);
        }
        return $this->giftCardConfiguration->getGiftCardOptionsData($data);
    }

    /**
     * @param string $code
     * @return bool|string
     */
    protected function _getCustomOption($code)
    {
        $item = $this->getItem();
        $option = $item->getOptionByCode($code);
        if ($option) {
            $value = $option->getValue();
            if ($value) {
                return $this->escapeHtml($value);
            }
        }
        return null;
    }
}
