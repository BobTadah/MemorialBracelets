<?php
namespace Aheadworks\Giftcard\Block\Order\Item;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Gift Card items Renderer
 * @package Aheadworks\Giftcard\Block\Order\Item
 */
class Renderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration
     */
    protected $giftCardConfiguration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $string,
            $productOptionFactory,
            $data
        );
        $this->giftCardConfiguration = $giftCardConfiguration;
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        return array_merge(
            $this->getGiftCardOptions(),
            parent::getItemOptions()
        );
    }

    /**
     * Get list of Gift Card options for product
     *
     * @return array
     */
    protected function getGiftCardOptions()
    {
        return $this->giftCardConfiguration->getGiftCardOptionsData(
            $this->getCustomOptions()
        );
    }

    /**
     * @return array|bool|string
     */
    protected function getCustomOptions()
    {
        $options = $this->getOrderItem()->getProductOptions();
        unset($options[TypeGiftCard::ATTRIBUTE_CODE_TYPE]);
        unset($options['aw_gc_created_codes']);
        return $options;
    }
}
